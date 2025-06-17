<?php

namespace tests\functional;

use FunctionalTester;

class BasicControllersCest
{
    /**
     * Тест доступности публичных страниц
     */
    public function testPublicPagesAccessible(FunctionalTester $I): void
    {
        $I->wantTo('проверить доступность публичных страниц');
        
        // Проверяю: Главная страница (/)
        $I->amOnPage('/');
        $I->seeResponseCodeIsSuccessful();
        $I->dontSee('Fatal error');
        $I->dontSee('Exception');
        $I->dontSee('Parse error');
        
        // Проверяю: Список книг (/book/index)
        $I->amOnPage('/book/index');
        $I->seeResponseCodeIsSuccessful();
        $I->dontSee('Fatal error');
        $I->dontSee('Exception');
        $I->dontSee('Parse error');
        
        // Проверяю: Список авторов (/author/index)
        $I->amOnPage('/author/index');
        $I->seeResponseCodeIsSuccessful();
        $I->dontSee('Fatal error');
        $I->dontSee('Exception');
        $I->dontSee('Parse error');
        
        // Проверяю: Отчеты (/report/top-authors)
        $I->amOnPage('/report/top-authors');
        $I->seeResponseCodeIsSuccessful();
        $I->dontSee('Fatal error');
        $I->dontSee('Exception');
        $I->dontSee('Parse error');
    }

    /**
     * Тест доступа к отчетам для всех пользователей (согласно ТЗ)
     */
    public function testGuestCanAccessReports(FunctionalTester $I): void
    {
        $I->wantTo('проверить, что отчеты доступны всем пользователям согласно ТЗ');
        
        $I->amOnPage('/report/top-authors');
        $I->seeResponseCodeIsSuccessful();
        
        // Проверяем, что страница отчета загружается
        $I->see('Топ-10 авторов', 'h1');
        $I->see('Год:');
        $I->seeElement('input[name="year"]');
        $I->seeElement('button[type="submit"]');
    }

    /**
     * Тест скрытия кнопок создания и редактирования для неавторизованных пользователей
     */
    public function testGuestCannotSeeActionButtons(FunctionalTester $I): void
    {
        $I->wantTo('проверить, что гости не видят кнопки создания, редактирования и удаления');
        
        // Проверяем страницу книг
        $I->amOnPage('/book/index');
        $I->seeResponseCodeIsSuccessful();
        $I->dontSee('Добавить книгу');
        $I->dontSeeElement('a[href*="/book/create"]');
        $I->dontSee('Редактировать');
        $I->dontSee('Удалить');
        $I->dontSeeElement('a[title="Редактировать"]');
        $I->dontSeeElement('a[title="Удалить"]');
        
        // Проверяем страницу авторов
        $I->amOnPage('/author/index');
        $I->seeResponseCodeIsSuccessful();
        $I->dontSee('Добавить автора');
        $I->dontSeeElement('a[href*="/author/create"]');
        $I->dontSee('Редактировать');
        $I->dontSee('Удалить');
        $I->dontSeeElement('a[title="Редактировать"]');
        $I->dontSeeElement('a[title="Удалить"]');
        
        // Проверяем, что ссылка на отчеты присутствует в навигации (доступна всем согласно ТЗ)
        $I->amOnPage('/');
        $I->see('Отчеты');
        $I->seeElement('a[href*="/report"]');
    }

    /**
     * Тест защиты страниц, требующих авторизации
     */
    public function testProtectedPagesRequireAuth(FunctionalTester $I): void
    {
        $I->wantTo('проверить, что защищенные страницы требуют авторизации');
        
        // Проверяю защиту: Создание автора (/author/create)
        $I->amOnPage('/author/create');
        $I->seeResponseCodeIs(403); // Ожидаем 403 Forbidden для неавторизованного пользователя
        
        // Проверяю защиту: Создание книги (/book/create)
        $I->amOnPage('/book/create');
        $I->seeResponseCodeIs(403); // Ожидаем 403 Forbidden для неавторизованного пользователя
    }

    /**
     * Тест наличия основных элементов интерфейса
     */
    public function testUIElementsPresent(FunctionalTester $I): void
    {
        $I->wantTo('проверить наличие основных элементов интерфейса');
        
        // Проверяем главную страницу
        $I->amOnPage('/');
        $I->seeElement('nav'); // Навигационное меню
        $I->see('Добро пожаловать'); // Заголовок главной страницы
        
        // Проверяем наличие ссылок в навигации для гостей
        $I->see('Главная');
        $I->see('Книги');
        $I->see('Авторы');
        // Согласно новому ТЗ RBAC - отчеты доступны всем
        $I->see('Отчеты');
        
        // В тестовом окружении достаточно проверить, что есть навигация и основные элементы
    }

    /**
     * Тест безопасности - проверка отсутствия отладочной информации
     */
    public function testNoDebugInfoLeakage(FunctionalTester $I): void
    {
        $I->wantTo('проверить отсутствие утечки отладочной информации');
        
        $pages = ['/', '/author/index', '/book/index', '/site/contact', '/report/top-authors'];
        
        foreach ($pages as $url) {
            $I->amOnPage($url);
            $I->dontSee('Database');
            $I->dontSee('Connection');
            $I->dontSee('SELECT');
            $I->dontSee('MySQL');
            $I->dontSee('Stack trace');
            $I->dontSee('vendor/');
        }
    }

    /**
     * Тест проверки времени отклика страниц
     */
    public function testPageLoadTime(FunctionalTester $I): void
    {
        $I->wantTo('проверить время загрузки основных страниц');
        
        $pages = ['/', '/author/index', '/book/index', '/report/top-authors'];
        
        foreach ($pages as $url) {
            $start = microtime(true);
            $I->amOnPage($url);
            $I->seeResponseCodeIsSuccessful();
            $end = microtime(true);
            
            $loadTime = $end - $start;
            $I->comment("Время загрузки {$url}: " . round($loadTime, 3) . " сек");
            
            // Страница должна загружаться менее чем за 10 секунд (для Docker среды)
            if ($loadTime > 10) {
                $I->fail("Страница {$url} загружается слишком медленно: {$loadTime} сек");
            }
        }
    }

    /**
     * Тест базовой навигации
     */
    public function testBasicNavigation(FunctionalTester $I): void
    {
        $I->wantTo('проверить базовую навигацию по сайту');
        
        // Начинаем с главной страницы
        $I->amOnPage('/');
        $I->seeResponseCodeIsSuccessful();
        
        // Переходим к авторам
        $I->click('Авторы');
        $I->seeInCurrentUrl('author');
        $I->seeResponseCodeIsSuccessful();
        
        // Переходим к книгам
        $I->click('Книги');
        $I->seeInCurrentUrl('book');
        $I->seeResponseCodeIsSuccessful();
        
        // Переходим к отчетам (доступны всем согласно ТЗ)
        $I->click('Отчеты');
        $I->seeInCurrentUrl('report');
        $I->seeResponseCodeIsSuccessful();
        $I->see('Топ-10 авторов', 'h1');
    }

    /**
     * Тест обработки несуществующих страниц
     */
    public function testNotFoundPages(FunctionalTester $I): void
    {
        $I->wantTo('проверить корректную обработку несуществующих страниц');
        
        // Проверяю обработку: /author/view?id=99999
        $I->amOnPage('/author/view?id=99999');
        $I->seeResponseCodeIs(404); // Ожидаем 404 Not Found для несуществующего автора
        
        // Проверяю обработку: /book/view?id=99999
        $I->amOnPage('/book/view?id=99999');
        $I->seeResponseCodeIs(404); // Ожидаем 404 Not Found для несуществующей книги
        
        // Проверяю обработку: несуществующий контроллер
        $I->amOnPage('/nonexistent/index');
        $I->seeResponseCodeIs(404); // Ожидаем 404 Not Found для несуществующего контроллера
    }
} 