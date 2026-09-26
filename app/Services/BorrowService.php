<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BorrowRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BorrowService
{
    public function borrow(User $user, Book $book): BorrowRecord
    {
        return DB::transaction(function () use ($user, $book) {
            $user = User::lockForUpdate()->findOrFail($user->id);
            $book = Book::lockForUpdate()->findOrFail($book->id);

            throw_if(! $user->isActive(), ValidationException::withMessages(['book' => 'Tài khoản không hoạt động nên không thể mượn sách.']));
            throw_if($user->role !== 'user', ValidationException::withMessages(['book' => 'Chỉ độc giả được mượn sách.']));
            throw_if(! $book->is_active || $book->available < 1, ValidationException::withMessages(['book' => 'Sách đã hết hoặc đang bị ẩn.']));

            $active = $user->borrowRecords()->whereIn('status', ['borrowing', 'overdue']);
            throw_if((clone $active)->where('book_id', $book->id)->exists(), ValidationException::withMessages(['book' => 'Bạn đang mượn cuốn sách này và chưa trả.']));
            throw_if((clone $active)->count() >= config('library.max_active_loans'), ValidationException::withMessages(['book' => 'Bạn chỉ được mượn tối đa 3 đầu sách cùng lúc.']));

            $today = today();
            $record = BorrowRecord::create([
                'user_id' => $user->id,
                'book_id' => $book->id,
                'borrow_date' => $today,
                'due_date' => $today->copy()->addDays(config('library.loan_days')),
                'status' => 'borrowing',
                'fine_amount' => 0,
                'is_paid' => true,
            ]);
            $book->decrement('available');

            return $record;
        }, 3);
    }

    public function returnBook(BorrowRecord $record, bool $finePaid = false): BorrowRecord
    {
        return DB::transaction(function () use ($record, $finePaid) {
            $record = BorrowRecord::lockForUpdate()->findOrFail($record->id);
            throw_if($record->status === 'returned', ValidationException::withMessages(['record' => 'Phiếu này đã được trả trước đó.']));
            $book = Book::lockForUpdate()->findOrFail($record->book_id);
            $returnDate = today();
            $lateDays = max(0, $record->due_date->startOfDay()->diffInDays($returnDate, false));
            $fine = $lateDays * config('library.fine_per_day');

            $record->update([
                'return_date' => $returnDate,
                'status' => 'returned',
                'fine_amount' => $fine,
                'is_paid' => $fine === 0 || $finePaid,
            ]);
            throw_if($book->available >= $book->total_quantity, ValidationException::withMessages(['record' => 'Số lượng sách hiện tại không hợp lệ.']));
            $book->increment('available');

            return $record->refresh();
        }, 3);
    }

    public function markFinePaid(BorrowRecord $record): void
    {
        throw_if($record->status !== 'returned' || $record->fine_amount <= 0, ValidationException::withMessages(['record' => 'Phiếu không có tiền phạt cần thu.']));
        $record->update(['is_paid' => true]);
    }
}
