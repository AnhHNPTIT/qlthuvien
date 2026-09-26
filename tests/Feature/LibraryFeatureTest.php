<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\BorrowRecord;
use App\Models\Category;
use App\Models\User;
use App\Services\BorrowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LibraryFeatureTest extends TestCase
{
    use RefreshDatabase;

    private static int $userSequence = 0;

    private static int $bookSequence = 0;

    private function user(array $data = []): User
    {
        $number = ++self::$userSequence;

        return User::create(array_merge(['user_code' => 'DG'.str_pad((string) $number, 4, '0', STR_PAD_LEFT), 'name' => 'Độc giả '.$number, 'email' => "user{$number}@test.local", 'password' => '12345678', 'role' => 'user', 'status' => 'active'], $data));
    }

    private function book(array $data = []): Book
    {
        $number = ++self::$bookSequence;
        $author = Author::create(['name' => 'Tác giả '.$number]);
        $category = Category::create(['name' => 'Thể loại '.$number]);

        return Book::create(array_merge(['book_code' => 'S'.str_pad((string) $number, 4, '0', STR_PAD_LEFT), 'title' => 'Sách '.$number, 'author_id' => $author->id, 'category_id' => $category->id, 'total_quantity' => 2, 'available' => 2, 'is_active' => true], $data));
    }

    public function test_public_can_search_and_only_sees_active_books(): void
    {
        $visible = $this->book(['title' => 'Mắt Biếc']);
        $this->book(['title' => 'Sách ẩn', 'is_active' => false]);
        $this->get('/books?q=Mắt')->assertOk()->assertSee($visible->book_code)->assertDontSee('Sách ẩn');
    }

    public function test_public_book_filters_include_author_category_and_reset_button(): void
    {
        $wanted = $this->book(['title' => 'Sách cần tìm']);
        $other = $this->book(['title' => 'Sách khác']);

        $this->get('/books?author_id='.$wanted->author_id.'&category_id='.$wanted->category_id)
            ->assertOk()
            ->assertSee($wanted->title)
            ->assertDontSee($other->title)
            ->assertSee('Bỏ lọc');
    }

    public function test_registration_generates_reader_code_and_hashes_password(): void
    {
        $this->post('/register', ['name' => 'Người mới', 'email' => 'new@example.com', 'password' => '12345678', 'password_confirmation' => '12345678'])->assertRedirect('/books');
        $user = User::where('email', 'new@example.com')->firstOrFail();
        $this->assertSame('DG0001', $user->user_code);
        $this->assertSame('user', $user->role);
        $this->assertNotSame('12345678', $user->getRawOriginal('password'));
    }

    public function test_locked_and_deleted_accounts_cannot_log_in(): void
    {
        foreach (['locked', 'deleted'] as $status) {
            $user = $this->user(['status' => $status]);
            $this->post('/login', ['email' => $user->email, 'password' => '12345678'])->assertSessionHasErrors('email');
            $this->assertGuest();
        }
    }

    public function test_reader_cannot_access_admin_urls(): void
    {
        $this->actingAs($this->user())->get('/admin/statistics')->assertForbidden();
    }

    public function test_borrow_creates_record_with_14_day_due_date_and_decrements_available(): void
    {
        Carbon::setTestNow('2026-09-01 08:00:00');
        $user = $this->user();
        $book = $this->book();
        app(BorrowService::class)->borrow($user, $book);
        $this->assertDatabaseHas('borrow_records', ['user_id' => $user->id, 'book_id' => $book->id, 'borrow_date' => '2026-09-01', 'due_date' => '2026-09-15', 'status' => 'borrowing']);
        $this->assertSame(1, $book->fresh()->available);
        Carbon::setTestNow();
    }

    public function test_duplicate_and_fourth_active_loans_are_rejected_without_changing_stock(): void
    {
        $service = app(BorrowService::class);
        $user = $this->user();
        $book = $this->book();
        $service->borrow($user, $book);
        try {
            $service->borrow($user, $book);
            $this->fail('Duplicate loan was accepted');
        } catch (ValidationException) {
        }
        $this->assertSame(1, $book->fresh()->available);
        $service->borrow($user, $this->book());
        $service->borrow($user, $this->book());
        $fourth = $this->book();
        try {
            $service->borrow($user, $fourth);
            $this->fail('Fourth loan was accepted');
        } catch (ValidationException) {
        }
        $this->assertSame(2, $fourth->fresh()->available);
    }

    public function test_hidden_or_unavailable_book_cannot_be_borrowed(): void
    {
        foreach ([['is_active' => false], ['available' => 0, 'total_quantity' => 1]] as $state) {
            try {
                app(BorrowService::class)->borrow($this->user(), $this->book($state));
                $this->fail('Invalid book was borrowed');
            } catch (ValidationException) {
            }
        }
        $this->assertDatabaseCount('borrow_records', 0);
    }

    public function test_late_return_calculates_fine_increments_stock_and_cannot_repeat(): void
    {
        Carbon::setTestNow('2026-09-01');
        $book = $this->book();
        $record = app(BorrowService::class)->borrow($this->user(), $book);
        Carbon::setTestNow('2026-09-18');
        app(BorrowService::class)->returnBook($record);
        $record->refresh();
        $this->assertSame('returned', $record->status);
        $this->assertSame(6000, $record->fine_amount);
        $this->assertFalse($record->is_paid);
        $this->assertSame(2, $book->fresh()->available);
        try {
            app(BorrowService::class)->returnBook($record);
            $this->fail('Repeated return was accepted');
        } catch (ValidationException) {
        }
        Carbon::setTestNow();
    }

    public function test_book_and_user_delete_are_soft_business_deletes(): void
    {
        $admin = $this->user(['user_code' => 'NV0001', 'email' => 'admin@test.local', 'role' => 'admin']);
        $reader = $this->user();
        $book = $this->book();
        $this->actingAs($admin)->delete(route('books.destroy', $book))->assertRedirect();
        $this->actingAs($admin)->delete(route('users.destroy', $reader))->assertRedirect();
        $this->assertDatabaseHas('books', ['id' => $book->id, 'is_active' => false]);
        $this->assertDatabaseHas('users', ['id' => $reader->id, 'status' => 'deleted']);
    }

    public function test_referenced_author_and_category_cannot_be_deleted(): void
    {
        $admin = $this->user(['user_code' => 'NV0001', 'email' => 'admin@test.local', 'role' => 'admin']);
        $book = $this->book(['is_active' => false]);
        $this->actingAs($admin)->delete(route('authors.destroy', $book->author))->assertSessionHas('error');
        $this->actingAs($admin)->delete(route('categories.destroy', $book->category))->assertSessionHas('error');
        $this->assertDatabaseHas('authors', ['id' => $book->author_id]);
        $this->assertDatabaseHas('categories', ['id' => $book->category_id]);
    }

    public function test_total_quantity_cannot_be_less_than_currently_borrowed_copies(): void
    {
        $admin = $this->user(['user_code' => 'NV0001', 'email' => 'admin@test.local', 'role' => 'admin']);
        $book = $this->book(['total_quantity' => 2, 'available' => 1]);
        BorrowRecord::create(['user_id' => $this->user()->id, 'book_id' => $book->id, 'borrow_date' => today(), 'due_date' => today()->addDays(14), 'status' => 'borrowing']);
        $response = $this->actingAs($admin)->put(route('books.update', $book), [
            'book_code' => $book->book_code, 'title' => $book->title, 'author_id' => $book->author_id,
            'category_id' => $book->category_id, 'total_quantity' => 0, 'is_active' => 1,
        ]);
        $response->assertSessionHasErrors('total_quantity');
        $this->assertSame(2, $book->fresh()->total_quantity);
    }

    public function test_admin_can_upload_cover_and_edit_does_not_change_book_status(): void
    {
        Storage::fake('public');
        $admin = $this->user(['user_code' => 'NV0001', 'email' => 'admin@test.local', 'role' => 'admin']);
        $author = Author::create(['name' => 'Tác giả ảnh']);
        $category = Category::create(['name' => 'Thể loại ảnh']);
        $image = UploadedFile::fake()->createWithContent('cover.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2nKsAAAAASUVORK5CYII='));

        $this->actingAs($admin)->post(route('books.store'), [
            'book_code' => 'COVER001', 'title' => 'Sách có ảnh', 'author_id' => $author->id,
            'category_id' => $category->id, 'total_quantity' => 3, 'cover_image' => $image,
        ])->assertRedirect(route('books.admin.index'));

        $book = Book::where('book_code', 'COVER001')->firstOrFail();
        Storage::disk('public')->assertExists($book->cover_image);
        $book->update(['is_active' => false]);

        $this->actingAs($admin)->put(route('books.update', $book), [
            'book_code' => $book->book_code, 'title' => 'Tên đã sửa', 'author_id' => $author->id,
            'category_id' => $category->id, 'total_quantity' => 3, 'is_active' => 1,
        ])->assertRedirect(route('books.admin.index'));

        $this->assertFalse($book->fresh()->is_active);
        $this->assertSame('Tên đã sửa', $book->fresh()->title);
    }

    public function test_admin_can_filter_and_toggle_hidden_book(): void
    {
        $admin = $this->user(['user_code' => 'NV0001', 'email' => 'admin@test.local', 'role' => 'admin']);
        $hidden = $this->book(['title' => 'Sách đang ẩn', 'is_active' => false]);
        $active = $this->book(['title' => 'Sách đang hiện']);

        $this->actingAs($admin)->get('/admin/books?status=hidden&author_id='.$hidden->author_id.'&category_id='.$hidden->category_id)
            ->assertOk()->assertSee($hidden->title)->assertDontSee($active->title)->assertSee('Hiện lại sách');
        $this->actingAs($admin)->patch(route('books.toggle-status', $hidden))->assertRedirect();
        $this->assertTrue($hidden->fresh()->is_active);
    }

    public function test_all_role_pages_render_successfully(): void
    {
        $admin = $this->user(['user_code' => 'NV0001', 'email' => 'admin@test.local', 'role' => 'admin']);
        $reader = $this->user();
        $book = $this->book();
        foreach (['/admin/statistics', '/admin/books', '/admin/books/create', '/admin/authors', '/admin/categories', '/admin/users', '/admin/borrow-records'] as $uri) {
            $this->actingAs($admin)->get($uri)->assertOk();
        }
        $this->actingAs($reader)->get('/history')->assertOk();
        $this->get(route('books.show', $book))->assertOk();
    }

    public function test_overdue_command_only_updates_past_borrowing_records(): void
    {
        $user = $this->user();
        $book1 = $this->book();
        $book2 = $this->book();
        $old = BorrowRecord::create(['user_id' => $user->id, 'book_id' => $book1->id, 'borrow_date' => today()->subDays(20), 'due_date' => today()->subDay(), 'status' => 'borrowing']);
        $future = BorrowRecord::create(['user_id' => $user->id, 'book_id' => $book2->id, 'borrow_date' => today(), 'due_date' => today()->addDay(), 'status' => 'borrowing']);
        $this->artisan('library:mark-overdue')->assertSuccessful();
        $this->assertSame('overdue', $old->fresh()->status);
        $this->assertSame('borrowing', $future->fresh()->status);
    }
}
