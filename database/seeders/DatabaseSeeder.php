<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\BorrowRecord;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create(['user_code' => 'NV0001', 'name' => 'Quản trị thư viện', 'email' => 'admin@example.com', 'password' => '12345678', 'role' => 'admin', 'status' => 'active', 'phone' => '0900000001', 'address' => 'Thư viện trung tâm']);
        $users = collect([
            ['DG0001', 'Độc giả Demo', 'user@example.com'],
            ['DG0002', 'Nguyễn Minh Anh', 'minhanh@example.com'],
            ['DG0003', 'Trần Hoàng Nam', 'hoangnam@example.com'],
            ['DG0004', 'Lê Thu Hà', 'thuha@example.com'],
            ['DG0005', 'Phạm Quốc Bảo', 'quocbao@example.com'],
        ])->map(fn ($item, $i) => User::create(['user_code' => $item[0], 'name' => $item[1], 'email' => $item[2], 'password' => '12345678', 'role' => 'user', 'status' => 'active', 'phone' => '09000000'.str_pad((string) ($i + 2), 2, '0', STR_PAD_LEFT)]));

        $authors = collect([
            ['Nguyễn Nhật Ánh', 'Nhà văn Việt Nam với nhiều tác phẩm dành cho tuổi mới lớn.'],
            ['Nam Cao', 'Nhà văn hiện thực tiêu biểu của văn học Việt Nam.'],
            ['Tô Hoài', 'Tác giả của nhiều tác phẩm văn học thiếu nhi nổi tiếng.'],
            ['Paulo Coelho', 'Tiểu thuyết gia người Brazil.'],
            ['Dale Carnegie', 'Tác giả và diễn giả về phát triển bản thân.'],
        ])->map(fn ($item) => Author::create(['name' => $item[0], 'biography' => $item[1]]));
        $categories = collect([
            ['Văn học Việt Nam', 'Các tác phẩm văn học trong nước.'], ['Văn học nước ngoài', 'Các tác phẩm văn học dịch.'],
            ['Kỹ năng sống', 'Sách phát triển bản thân và kỹ năng.'], ['Thiếu nhi', 'Sách dành cho thiếu nhi.'], ['Khoa học', 'Kiến thức khoa học phổ thông.'],
        ])->map(fn ($item) => Category::create(['name' => $item[0], 'description' => $item[1]]));

        $titles = [
            ['Cho Tôi Xin Một Vé Đi Tuổi Thơ', 0, 0], ['Mắt Biếc', 0, 0], ['Tôi Thấy Hoa Vàng Trên Cỏ Xanh', 0, 0], ['Cô Gái Đến Từ Hôm Qua', 0, 3],
            ['Chí Phèo', 1, 0], ['Lão Hạc', 1, 0], ['Đời Thừa', 1, 0], ['Dế Mèn Phiêu Lưu Ký', 2, 3], ['Vợ Chồng A Phủ', 2, 0],
            ['Nhà Giả Kim', 3, 1], ['Bên Bờ Sông Piedra Tôi Ngồi Khóc', 3, 1], ['Đắc Nhân Tâm', 4, 2], ['Quẳng Gánh Lo Đi Và Vui Sống', 4, 2],
            ['Vũ Trụ Trong Vỏ Hạt Dẻ', 3, 4], ['Lược Sử Thời Gian', 3, 4], ['Muôn Kiếp Nhân Sinh', 4, 2], ['Không Gia Đình', 3, 1], ['Hoàng Tử Bé', 3, 3],
        ];
        $books = collect($titles)->map(function ($item, $i) use ($authors, $categories) {
            return Book::create(['book_code' => 'S'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT), 'title' => $item[0], 'author_id' => $authors[$item[1]]->id, 'category_id' => $categories[$item[2]]->id, 'publisher' => 'NXB Trẻ', 'publication_year' => 2020 + ($i % 5), 'total_quantity' => 5 + ($i % 4), 'available' => 5 + ($i % 4), 'is_active' => true, 'description' => 'Sách demo phục vụ trình diễn hệ thống quản lý thư viện.']);
        });

        BorrowRecord::create(['user_id' => $users[1]->id, 'book_id' => $books[0]->id, 'borrow_date' => today()->subDays(20), 'due_date' => today()->subDays(6), 'status' => 'overdue', 'fine_amount' => 0, 'is_paid' => true]);
        $books[0]->decrement('available');
        BorrowRecord::create(['user_id' => $users[2]->id, 'book_id' => $books[1]->id, 'borrow_date' => today()->subDays(5), 'due_date' => today()->addDays(9), 'status' => 'borrowing', 'fine_amount' => 0, 'is_paid' => true]);
        $books[1]->decrement('available');
        BorrowRecord::create(['user_id' => $users[0]->id, 'book_id' => $books[2]->id, 'borrow_date' => today()->subDays(30), 'due_date' => today()->subDays(16), 'return_date' => today()->subDays(14), 'status' => 'returned', 'fine_amount' => 4000, 'is_paid' => false]);
    }
}
