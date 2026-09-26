<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BorrowRecord;
use App\Services\BorrowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BorrowController extends Controller
{
    public function __construct(private readonly BorrowService $service) {}

    public function borrow(Request $request, Book $book): RedirectResponse
    {
        $this->service->borrow($request->user(), $book);

        return back()->with('success', 'Mượn sách thành công. Hạn trả là '.today()->addDays(config('library.loan_days'))->format('d/m/Y').'.');
    }

    public function index(Request $request): View
    {
        $query = BorrowRecord::with(['user', 'book'])->latest('borrow_date');
        $query->when(in_array($request->status, ['borrowing', 'returned', 'overdue'], true), fn ($q) => $q->where('status', $request->status));
        $query->when($request->boolean('unpaid'), fn ($q) => $q->where('fine_amount', '>', 0)->where('is_paid', false));
        $query->when($request->filled('q'), fn ($q) => $q->where(function ($sub) use ($request) {
            $term = '%'.$request->q.'%';
            $sub->whereHas('user', fn ($u) => $u->where('user_code', 'like', $term))->orWhereHas('book', fn ($b) => $b->where('book_code', 'like', $term));
        }));

        return view('borrow.index', ['records' => $query->paginate(15)->withQueryString()]);
    }

    public function returnBook(Request $request, BorrowRecord $borrowRecord): RedirectResponse
    {
        $this->service->returnBook($borrowRecord, $request->boolean('is_paid'));

        return back()->with('success', 'Đã xác nhận trả sách.');
    }

    public function pay(BorrowRecord $borrowRecord): RedirectResponse
    {
        $this->service->markFinePaid($borrowRecord);

        return back()->with('success', 'Đã xác nhận thu tiền phạt.');
    }
}
