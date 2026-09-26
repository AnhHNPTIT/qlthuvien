<?php

use App\Models\BorrowRecord;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('library:mark-overdue', function () {
    $count = BorrowRecord::where('status', 'borrowing')
        ->whereDate('due_date', '<', today())
        ->update(['status' => 'overdue']);
    $this->info("Đã cập nhật {$count} phiếu quá hạn.");
})->purpose('Chuyển các phiếu mượn quá hạn sang trạng thái overdue');

Schedule::command('library:mark-overdue')->daily();
