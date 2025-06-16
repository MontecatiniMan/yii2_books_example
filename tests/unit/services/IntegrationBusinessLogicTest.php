<?php

declare(strict_types=1);

namespace tests\unit\services;

use app\models\Author;
use app\models\AuthorSubscription;
use app\models\Book;
use app\services\interfaces\BookServiceInterface;
use app\services\interfaces\AuthorServiceInterface;
use app\services\interfaces\ReportServiceInterface;
use app\services\interfaces\SubscriptionServiceInterface;
use Codeception\Test\Unit;
use tests\fixtures\AuthorFixture;
use tests\fixtures\AuthorSubscriptionFixture;
use tests\fixtures\BookFixture;
use tests\fixtures\BookAuthorFixture;
use tests\fixtures\UserFixture;
use UnitTester;
use Yii;

/**
 * Полноценный интеграционный тест бизнес-логики с реальной БД
 * Проверяет основные сценарии согласно ТЗ:
 * - Создание книги с отправкой SMS уведомлений
 * - Подписка на автора 
 * - Получение отчета ТОП-10 авторов
 * - Управление авторами и книгами
 * 
 * Использует фикстуры и автоматическую очистку БД перед каждым тестом
 */
class IntegrationBusinessLogicTest extends Unit
{
    protected UnitTester $tester;

    private BookServiceInterface $bookService;
    private AuthorServiceInterface $authorService;
    private ReportServiceInterface $reportService;
    private SubscriptionServiceInterface $subscriptionService;

    public function _fixtures(): array
    {
        return [
            'users' => UserFixture::class,
            'authors' => AuthorFixture::class,
            'books' => BookFixture::class,
            'book_authors' => BookAuthorFixture::class,
            'subscriptions' => AuthorSubscriptionFixture::class,
        ];
    }

    protected function _before(): void
    {
        parent::_before();
        
        // Получаем сервисы из DI контейнера
        $this->bookService = Yii::$container->get(BookServiceInterface::class);
        $this->authorService = Yii::$container->get(AuthorServiceInterface::class);
        $this->reportService = Yii::$container->get(ReportServiceInterface::class);
        $this->subscriptionService = Yii::$container->get(SubscriptionServiceInterface::class);
    }

    /**
     * Тест создания книги с авторами (основной сценарий ТЗ)
     */
    public function testCreateBookWithAuthors(): void
    {
        // Создаем новую книгу
        $book = new Book();
        $book->title = 'Новая тестовая книга';
        $book->description = 'Описание тестовой книги';
        $book->publication_year = 2023;
        $book->isbn = '9785389999999';

        // Указываем авторов (Лев Толстой и Александр Пушкин)
        $authorIds = [1, 2];

        // Создаем книгу с авторами
        $createdBook = $this->bookService->createBookWithAuthors($book, $authorIds);

        // Проверки
        $this->assertNotNull($createdBook->id, 'Книга должна быть сохранена с ID');
        $this->assertEquals('Новая тестовая книга', $createdBook->title);
        $this->assertEquals(2023, $createdBook->publication_year);
        $this->assertEquals('9785389999999', $createdBook->isbn);

        // Проверяем связи с авторами
        $bookAuthors = $this->bookService->getBookAuthorIds($createdBook->id);
        $this->assertCount(2, $bookAuthors, 'Книга должна быть связана с 2 авторами');
        $this->assertContains(1, $bookAuthors, 'Книга должна быть связана с Львом Толстым');
        $this->assertContains(2, $bookAuthors, 'Книга должна быть связана с Александром Пушкиным');

        // Проверяем что книга создана в БД
        $savedBook = Book::findOne($createdBook->id);
        $this->assertNotNull($savedBook, 'Книга должна быть сохранена в БД');
        $this->assertEquals('Новая тестовая книга', $savedBook->title);
    }

    /**
     * Тест подписки на автора для гостя (сценарий ТЗ)
     */
    public function testAuthorSubscriptionForGuest(): void
    {
        $authorId = 1; // Лев Толстой
        $phone = '+79165555555';
        $userId = null; // Гость

        // Подписываемся на автора
        $result = $this->subscriptionService->subscribe($authorId, $phone, $userId);

        // Проверки
        $this->assertTrue($result, 'Подписка должна быть успешно создана');

        // Проверяем что подписка создана в БД
        $subscription = AuthorSubscription::findOne(['author_id' => $authorId, 'phone' => $phone]);
        $this->assertNotNull($subscription, 'Подписка должна быть сохранена в БД');
        $this->assertEquals($authorId, $subscription->author_id);
        $this->assertEquals($phone, $subscription->phone);
        $this->assertNull($subscription->user_id, 'user_id должен быть null для гостя');
    }

    /**
     * Тест подписки на автора для авторизованного пользователя
     */
    public function testAuthorSubscriptionForUser(): void
    {
        $authorId = 2; // Александр Пушкин
        $phone = '+79166666666';
        $userId = 1; // admin

        // Подписываемся на автора
        $result = $this->subscriptionService->subscribe($authorId, $phone, $userId);

        // Проверки
        $this->assertTrue($result, 'Подписка должна быть успешно создана');

        // Проверяем что подписка создана в БД
        $subscription = AuthorSubscription::findOne(['author_id' => $authorId, 'phone' => $phone]);
        $this->assertNotNull($subscription, 'Подписка должна быть сохранена в БД');
        $this->assertEquals($authorId, $subscription->author_id);
        $this->assertEquals($phone, $subscription->phone);
        $this->assertEquals($userId, $subscription->user_id);
    }

    /**
     * Тест отписки от автора
     */
    public function testUnsubscribeFromAuthor(): void
    {
        $authorId = 1; // Лев Толстой
        $phone = '+79161111111'; // Существующая подписка из фикстуры

        // Проверяем что подписка существует
        $subscription = AuthorSubscription::findOne(['author_id' => $authorId, 'phone' => $phone]);
        $this->assertNotNull($subscription, 'Подписка должна существовать в фикстуре');

        // Отписываемся
        $result = $this->subscriptionService->unsubscribe($authorId, $phone);

        // Проверки
        $this->assertTrue($result, 'Отписка должна быть успешной');

        // Проверяем что подписка удалена из БД
        $subscription = AuthorSubscription::findOne(['author_id' => $authorId, 'phone' => $phone]);
        $this->assertNull($subscription, 'Подписка должна быть удалена из БД');
    }

    /**
     * Тест получения ТОП-10 авторов за год (отчет согласно ТЗ)
     */
    public function testTopAuthorsReportByYear(): void
    {
        $year = 1869; // Год издания "Войны и мира"

        // Получаем топ авторов
        $topAuthors = $this->reportService->getTopAuthorsByYear($year, 10);

        // Проверки
        $this->assertIsArray($topAuthors, 'Результат должен быть массивом');
        $this->assertNotEmpty($topAuthors, 'Должен быть хотя бы один автор');

        // Проверяем структуру первого автора
        $firstAuthor = $topAuthors[0];
        $this->assertInstanceOf(Author::class, $firstAuthor, 'Результат должен содержать объекты Author');
        $this->assertNotEmpty($firstAuthor->name, 'Автор должен иметь имя');
        $this->assertObjectHasProperty('book_count', $firstAuthor, 'Автор должен иметь количество книг');
        $this->assertIsInt($firstAuthor->book_count, 'Количество книг должно быть числом');
        $this->assertGreaterThan(0, $firstAuthor->book_count, 'Количество книг должно быть больше 0');
    }

    /**
     * Тест обновления книги с изменением авторов
     */
    public function testUpdateBookWithAuthors(): void
    {
        $bookId = 1; // Война и мир (изначально у Льва Толстого)

        // Создаем данные для обновления
        $bookData = new Book();
        $bookData->title = 'Война и мир (обновленное издание)';
        $bookData->description = 'Обновленное описание';
        $bookData->publication_year = 1869;
        $bookData->isbn = '9785389145671';

        // Меняем авторов на Пушкина и Достоевского
        $newAuthorIds = [2, 3];

        // Обновляем книгу
        $updatedBook = $this->bookService->updateBookWithAuthors($bookId, $bookData, $newAuthorIds);

        // Проверки
        $this->assertEquals('Война и мир (обновленное издание)', $updatedBook->title);
        $this->assertEquals('Обновленное описание', $updatedBook->description);

        // Проверяем новые связи с авторами
        $bookAuthors = $this->bookService->getBookAuthorIds($bookId);
        $this->assertCount(2, $bookAuthors, 'Книга должна быть связана с 2 новыми авторами');
        $this->assertContains(2, $bookAuthors, 'Книга должна быть связана с Пушкиным');
        $this->assertContains(3, $bookAuthors, 'Книга должна быть связана с Достоевским');
        $this->assertNotContains(1, $bookAuthors, 'Книга не должна быть связана с Толстым');
    }

    /**
     * Тест создания и управления авторами
     */
    public function testAuthorManagement(): void
    {
        // Создаем нового автора
        $author = new Author();
        $author->name = 'Иван Тургенев';

        $createdAuthor = $this->authorService->createAuthor($author);

        // Проверки создания
        $this->assertNotNull($createdAuthor->id, 'Автор должен быть сохранен с ID');
        $this->assertEquals('Иван Тургенев', $createdAuthor->name);

        // Получаем автора по ID
        $foundAuthor = $this->authorService->getAuthor($createdAuthor->id);
        $this->assertEquals('Иван Тургенев', $foundAuthor->name);

        // Обновляем автора
        $foundAuthor->name = 'Иван Сергеевич Тургенев';
        $updatedAuthor = $this->authorService->updateAuthor($foundAuthor->id, $foundAuthor);
        $this->assertEquals('Иван Сергеевич Тургенев', $updatedAuthor->name);

        // Получаем всех авторов
        $allAuthors = $this->authorService->getAllAuthors();
        $this->assertGreaterThanOrEqual(5, count($allAuthors), 'Должно быть минимум 5 авторов (4 из фикстуры + 1 созданный)');

        // Удаляем автора
        $this->authorService->deleteAuthor($createdAuthor->id);

        // Проверяем что автор удален
        $deletedAuthor = Author::findOne($createdAuthor->id);
        $this->assertNull($deletedAuthor, 'Автор должен быть удален из БД');
    }

    /**
     * Тест валидации бизнес-правил
     */
    public function testBusinessRulesValidation(): void
    {
        // Тест создания книги с некорректными данными
        $book = new Book();
        $book->title = ''; // Пустое название
        $book->publication_year = 3000; // Будущий год
        $book->isbn = 'invalid-isbn';

        $this->assertFalse($book->validate(), 'Книга с некорректными данными не должна проходить валидацию');
        $this->assertArrayHasKey('title', $book->errors, 'Должна быть ошибка по полю title');
        $this->assertArrayHasKey('publication_year', $book->errors, 'Должна быть ошибка по полю publication_year');

        // Тест создания подписки с некорректным телефоном
        $subscription = new AuthorSubscription();
        $subscription->author_id = 1;
        $subscription->phone = 'invalid-phone';

        $this->assertFalse($subscription->validate(), 'Подписка с некорректным телефоном не должна проходить валидацию');
        $this->assertArrayHasKey('phone', $subscription->errors, 'Должна быть ошибка по полю phone');
    }

    /**
     * Тест уникальности ISBN
     */
    public function testIsbnUniqueness(): void
    {
        // Пытаемся создать книгу с существующим ISBN
        $book = new Book();
        $book->title = 'Другая книга';
        $book->description = 'Описание';
        $book->publication_year = 2023;
        $book->isbn = '9785389145678'; // ISBN из фикстуры (Война и мир)

        $this->assertFalse($book->validate(), 'Книга с дублирующимся ISBN не должна проходить валидацию');
        $this->assertArrayHasKey('isbn', $book->errors, 'Должна быть ошибка уникальности ISBN');
    }

    /**
     * Тест работы с книгами по автору
     */
    public function testBooksByAuthor(): void
    {
        $authorId = 1; // Лев Толстой

        // Получаем книги автора
        $authorBooks = $this->bookService->getBooksByAuthor($authorId);

        // Проверки
        $this->assertIsArray($authorBooks, 'Результат должен быть массивом');
        $this->assertCount(2, $authorBooks, 'У Льва Толстого должно быть 2 книги в фикстуре');

        // Проверяем что все книги действительно принадлежат автору
        foreach ($authorBooks as $book) {
            $this->assertInstanceOf(Book::class, $book, 'Элемент должен быть экземпляром Book');
            
            // Проверяем связь через авторов книги
            $authors = $book->authors;
            $authorIds = array_column($authors, 'id');
            $this->assertContains($authorId, $authorIds, 'Книга должна быть связана с автором');
        }
    }

    /**
     * Тест подсчета подписчиков у автора
     */
    public function testAuthorSubscribersCount(): void
    {
        $authorId = 1; // Лев Толстой

        // Из фикстуры у него должно быть 2 подписки
        $subscriptions = AuthorSubscription::findAll(['author_id' => $authorId]);
        $this->assertCount(2, $subscriptions, 'У Льва Толстого должно быть 2 подписки из фикстуры');

        // Добавляем еще одну подписку
        $this->subscriptionService->subscribe($authorId, '+79167777777', null);

        // Проверяем что стало 3
        $subscriptions = AuthorSubscription::findAll(['author_id' => $authorId]);
        $this->assertCount(3, $subscriptions, 'После добавления должно быть 3 подписки');
    }

    /**
     * Тест транзакционности операций
     */
    public function testTransactionality(): void
    {
        $initialBookCount = Book::find()->count();

        // Пытаемся создать книгу с несуществующими авторами
        $book = new Book();
        $book->title = 'Тестовая книга';
        $book->description = 'Описание';
        $book->publication_year = 2023;
        $book->isbn = '9785389000000';

        $nonExistentAuthorIds = [999, 1000]; // Несуществующие авторы

        try {
            $this->bookService->createBookWithAuthors($book, $nonExistentAuthorIds);
            $this->fail('Должно было быть исключение при создании книги с несуществующими авторами');
        } catch (\Exception $e) {
            // Ожидаемое поведение
        }

        // Проверяем что количество книг не изменилось (транзакция откатилась)
        $finalBookCount = Book::find()->count();
        $this->assertEquals($initialBookCount, $finalBookCount, 'Количество книг не должно измениться при ошибке');
    }
} 