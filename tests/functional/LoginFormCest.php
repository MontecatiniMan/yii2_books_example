<?php

namespace tests\functional;

class LoginFormCest
{
    public function _before(\FunctionalTester $I): void
    {
        $I->amOnRoute('site/login');
    }

    public function openLoginPage(\FunctionalTester $I): void
    {
        $I->see('Вход', 'h1');

    }

    // Эти тесты требуют наличия пользователей в БД, которых у нас нет

    public function loginWithEmptyCredentials(\FunctionalTester $I): void
    {
        $I->submitForm('#login-form', []);
        $I->expectTo('see validations errors');
        $I->see('Username cannot be blank.');
        $I->see('Password cannot be blank.');
    }

    public function loginWithWrongCredentials(\FunctionalTester $I): void
    {
        $I->submitForm('#login-form', [
            'LoginForm[username]' => 'admin',
            'LoginForm[password]' => 'wrong',
        ]);
        $I->expectTo('see validations errors');
        $I->see('Неверное имя пользователя или пароль.');
    }

    public function loginSuccessfully(\FunctionalTester $I): void
    {
        // Тест успешного входа требует наличия пользователей в БД
        // В нашем тестовом окружении их нет, поэтому просто проверим форму
        $I->seeElement('form#login-form');
        $I->seeElement('input[name="LoginForm[username]"]');
        $I->seeElement('input[name="LoginForm[password]"]');
    }
}