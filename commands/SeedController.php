<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Exception;

/**
 * Контроллер для заполнения БД образцовыми данными
 */
class SeedController extends Controller
{
    /**
     * Заполняет БД образцовыми данными книг и авторов
     * @return int
     */
    public function actionIndex(): int
    {
        $this->stdout("🌱 Начинаем заполнение БД образцовыми данными...\n");

        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Проверяем, есть ли уже данные
            $authorCount = Yii::$app->db->createCommand('SELECT COUNT(*) FROM {{%authors}}')->queryScalar();
            $bookCount = Yii::$app->db->createCommand('SELECT COUNT(*) FROM {{%books}}')->queryScalar();
            
            if ($authorCount > 0 || $bookCount > 0) {
                $this->stdout("⚠️  В БД уже есть данные. Очистить и заполнить заново? (y/N): ");
                $handle = fopen("php://stdin", "r");
                $line = fgets($handle);
                fclose($handle);
                
                if (trim(strtolower($line)) !== 'y') {
                    $this->stdout("❌ Операция отменена.\n");
                    return ExitCode::OK;
                }
                
                $this->actionClear();
            }

            $this->seedAuthors();
            $this->seedBooks();
            $this->seedBookAuthors();
            $this->seedTestUser();
            $this->seedSubscriptions();

            $transaction->commit();
            $this->stdout("✅ Заполнение БД завершено успешно!\n");
            $this->printStatistics();
            
            return ExitCode::OK;
            
        } catch (Exception $e) {
            $transaction->rollBack();
            $this->stderr("❌ Ошибка при заполнении БД: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Очищает все данные из БД
     * @return int
     */
    public function actionClear(): int
    {
        $this->stdout("🧹 Очищаем БД от данных...\n");

        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Удаляем данные в обратном порядке (из-за внешних ключей)
            Yii::$app->db->createCommand()->delete('{{%author_subscriptions}}')->execute();
            Yii::$app->db->createCommand()->delete('{{%book_author}}')->execute();
            Yii::$app->db->createCommand()->delete('{{%books}}')->execute();
            Yii::$app->db->createCommand()->delete('{{%authors}}')->execute();
            Yii::$app->db->createCommand()->delete('{{%user}}', ['username' => 'testuser'])->execute();

            $transaction->commit();
            $this->stdout("✅ БД очищена успешно!\n");
            
            return ExitCode::OK;
            
        } catch (Exception $e) {
            $transaction->rollBack();
            $this->stderr("❌ Ошибка при очистке БД: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Показывает статистику данных в БД
     * @return int
     */
    public function actionStats(): int
    {
        $this->printStatistics();
        return ExitCode::OK;
    }

    /**
     * Заполняет таблицу авторов
     */
    private function seedAuthors(): void
    {
        $this->stdout("📚 Создаем авторов...\n");
        
        $currentTime = time();
        $authors = [
            ['name' => 'Лев Толстой', 'created_at' => $currentTime, 'updated_at' => $currentTime],
            ['name' => 'Федор Достоевский', 'created_at' => $currentTime, 'updated_at' => $currentTime],
            ['name' => 'Антон Чехов', 'created_at' => $currentTime, 'updated_at' => $currentTime],
            ['name' => 'Александр Пушкин', 'created_at' => $currentTime, 'updated_at' => $currentTime],
            ['name' => 'Николай Гоголь', 'created_at' => $currentTime, 'updated_at' => $currentTime],
            ['name' => 'Иван Тургенев', 'created_at' => $currentTime, 'updated_at' => $currentTime]
        ];

        foreach ($authors as $author) {
            Yii::$app->db->createCommand()->insert('{{%authors}}', $author)->execute();
            $this->stdout("  ✓ Создан автор: {$author['name']}\n");
        }
    }

    /**
     * Заполняет таблицу книг
     */
    private function seedBooks(): void
    {
        $this->stdout("📖 Создаем книги...\n");
        
        $currentTime = time();
        $books = [
            [
                'title' => 'Война и мир',
                'description' => 'Роман-эпопея о русском обществе в эпоху войн против Наполеона в 1805-1812 годах.',
                'publication_year' => 1869,
                'isbn' => '9785171234567',
                'cover_image' => 'war_and_peace.jpg',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'title' => 'Анна Каренина',
                'description' => 'Роман о трагической любви замужней дамы Анны Карениной и блестящего офицера Вронского.',
                'publication_year' => 1877,
                'isbn' => '9785171234568',
                'cover_image' => 'anna_karenina.jpg',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'title' => 'Преступление и наказание',
                'description' => 'Роман о студенте Раскольникове, совершившем убийство старухи-процентщицы.',
                'publication_year' => 1866,
                'isbn' => '9785171234569',
                'cover_image' => 'crime_and_punishment.jpg',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'title' => 'Братья Карамазовы',
                'description' => 'Последний роман Достоевского о семье Карамазовых и убийстве отца семейства.',
                'publication_year' => 1880,
                'isbn' => '9785171234570',
                'cover_image' => 'brothers_karamazov.jpg',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'title' => 'Вишневый сад',
                'description' => 'Последняя пьеса Чехова о разорении дворянской семьи.',
                'publication_year' => 1904,
                'isbn' => '9785171234571',
                'cover_image' => 'cherry_orchard.jpg',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'title' => 'Дядя Ваня',
                'description' => 'Пьеса Чехова о провинциальной жизни и несбывшихся мечтах.',
                'publication_year' => 1897,
                'isbn' => '9785171234572',
                'cover_image' => 'uncle_vanya.jpg',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'title' => 'Евгений Онегин',
                'description' => 'Роман в стихах о молодом дворянине и его любовной истории.',
                'publication_year' => 1833,
                'isbn' => '9785171234573',
                'cover_image' => 'eugene_onegin.jpg',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'title' => 'Капитанская дочка',
                'description' => 'Исторический роман о Пугачевском восстании.',
                'publication_year' => 1836,
                'isbn' => '9785171234574',
                'cover_image' => 'captains_daughter.jpg',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'title' => 'Мертвые души',
                'description' => 'Поэма о похождениях Павла Ивановича Чичикова.',
                'publication_year' => 1842,
                'isbn' => '9785171234575',
                'cover_image' => 'dead_souls.jpg',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'title' => 'Ревизор',
                'description' => 'Комедия о чиновничьих нравах в провинциальном городе.',
                'publication_year' => 1836,
                'isbn' => '9785171234576',
                'cover_image' => 'inspector.jpg',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'title' => 'Отцы и дети',
                'description' => 'Роман о конфликте поколений и нигилизме.',
                'publication_year' => 1862,
                'isbn' => '9785171234577',
                'cover_image' => 'fathers_and_sons.jpg',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'title' => 'Дворянское гнездо',
                'description' => 'Роман о любви и долге в дворянской среде.',
                'publication_year' => 1859,
                'isbn' => '9785171234578',
                'cover_image' => 'nest_of_gentlefolk.jpg',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ]
        ];

        foreach ($books as $book) {
            Yii::$app->db->createCommand()->insert('{{%books}}', $book)->execute();
            $this->stdout("  ✓ Создана книга: {$book['title']}\n");
        }
    }

    /**
     * Создает связи книг с авторами
     */
    private function seedBookAuthors(): void
    {
        $this->stdout("🔗 Создаем связи книг с авторами...\n");
        
        $currentTime = time();
        
        // Получаем реальные ID из БД
        $authors = Yii::$app->db->createCommand('SELECT id, name FROM {{%authors}} ORDER BY id')->queryAll();
        $books = Yii::$app->db->createCommand('SELECT id, title FROM {{%books}} ORDER BY id')->queryAll();
        
        // Создаем массив для удобного поиска
        $authorIds = [];
        foreach ($authors as $author) {
            $authorIds[$author['name']] = $author['id'];
        }
        
        $bookIds = [];
        foreach ($books as $book) {
            $bookIds[$book['title']] = $book['id'];
        }

        // Создаем связи
        $bookAuthors = [
            // Толстой
            ['book_title' => 'Война и мир', 'author_name' => 'Лев Толстой'],
            ['book_title' => 'Анна Каренина', 'author_name' => 'Лев Толстой'],
            
            // Достоевский
            ['book_title' => 'Преступление и наказание', 'author_name' => 'Федор Достоевский'],
            ['book_title' => 'Братья Карамазовы', 'author_name' => 'Федор Достоевский'],
            
            // Чехов
            ['book_title' => 'Вишневый сад', 'author_name' => 'Антон Чехов'],
            ['book_title' => 'Дядя Ваня', 'author_name' => 'Антон Чехов'],
            
            // Пушкин
            ['book_title' => 'Евгений Онегин', 'author_name' => 'Александр Пушкин'],
            ['book_title' => 'Капитанская дочка', 'author_name' => 'Александр Пушкин'],
            
            // Гоголь
            ['book_title' => 'Мертвые души', 'author_name' => 'Николай Гоголь'],
            ['book_title' => 'Ревизор', 'author_name' => 'Николай Гоголь'],
            
            // Тургенев
            ['book_title' => 'Отцы и дети', 'author_name' => 'Иван Тургенев'],
            ['book_title' => 'Дворянское гнездо', 'author_name' => 'Иван Тургенев'],
        ];

        foreach ($bookAuthors as $relation) {
            $bookId = $bookIds[$relation['book_title']] ?? null;
            $authorId = $authorIds[$relation['author_name']] ?? null;
            
            if ($bookId && $authorId) {
                Yii::$app->db->createCommand()->insert('{{%book_author}}', [
                    'book_id' => $bookId,
                    'author_id' => $authorId,
                    'created_at' => $currentTime
                ])->execute();
                
                $this->stdout("  ✓ Связана книга '{$relation['book_title']}' с автором '{$relation['author_name']}'\n");
            }
        }
    }

    /**
     * Создает тестового пользователя
     */
    private function seedTestUser(): void
    {
        $this->stdout("👤 Создаем тестового пользователя...\n");
        
        $currentTime = time();
        
        Yii::$app->db->createCommand()->insert('{{%user}}', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password_hash' => Yii::$app->security->generatePasswordHash('password'),
            'auth_key' => Yii::$app->security->generateRandomString(),
            'status' => 10, // STATUS_ACTIVE
            'created_at' => $currentTime,
            'updated_at' => $currentTime,
        ])->execute();

        $this->stdout("  ✓ Создан пользователь: testuser (пароль: password)\n");
    }

    /**
     * Создает образцовые подписки
     */
    private function seedSubscriptions(): void
    {
        $this->stdout("📧 Создаем образцовые подписки на авторов...\n");
        
        $currentTime = time();
        
        // Получаем ID пользователя и авторов
        $userId = Yii::$app->db->createCommand('SELECT id FROM {{%user}} WHERE username = :username', [':username' => 'testuser'])->queryScalar();
        $authors = Yii::$app->db->createCommand('SELECT id, name FROM {{%authors}} LIMIT 3')->queryAll();

        $phones = ['+7 (999) 123-45-67', '+7 (999) 234-56-78', '+7 (999) 345-67-89'];
        
        foreach ($authors as $index => $author) {
            Yii::$app->db->createCommand()->insert('{{%author_subscriptions}}', [
                'user_id' => $userId,
                'author_id' => $author['id'],
                'phone' => $phones[$index],
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ])->execute();
            
            $this->stdout("  ✓ Создана подписка на автора '{$author['name']}' с номером {$phones[$index]}\n");
        }
    }

    /**
     * Выводит статистику данных в БД
     */
    private function printStatistics(): void
    {
        $this->stdout("\n📊 Статистика данных в БД:\n");
        
        $authorCount = Yii::$app->db->createCommand('SELECT COUNT(*) FROM {{%authors}}')->queryScalar();
        $bookCount = Yii::$app->db->createCommand('SELECT COUNT(*) FROM {{%books}}')->queryScalar();
        $relationCount = Yii::$app->db->createCommand('SELECT COUNT(*) FROM {{%book_author}}')->queryScalar();
        $userCount = Yii::$app->db->createCommand('SELECT COUNT(*) FROM {{%user}}')->queryScalar();
        $subscriptionCount = Yii::$app->db->createCommand('SELECT COUNT(*) FROM {{%author_subscriptions}}')->queryScalar();
        
        $this->stdout("  📚 Авторы: {$authorCount}\n");
        $this->stdout("  📖 Книги: {$bookCount}\n");
        $this->stdout("  🔗 Связи книга-автор: {$relationCount}\n");
        $this->stdout("  👤 Пользователи: {$userCount}\n");
        $this->stdout("  📧 Подписки: {$subscriptionCount}\n");
    }
} 