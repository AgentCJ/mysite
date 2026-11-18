<?php
/** @var array $user */
/** @var array $errors */

$form_class = $errors ? 'form form--invalid' : 'form';
?>

<main>
    <form class="<?= $form_class ?> container" action="/login.php" method="post">
        <h2>Вход</h2>

        <div class="form__item<?= isset($errors['email']) ? ' form__item--invalid' : '' ?>">
            <label for="email">E-mail <sup>*</sup></label>
            <input
                id="email"
                type="text"
                name="email"
                placeholder="Введите e-mail"
                value="<?= htmlspecialchars($user['email'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
            >
            <span class="form__error"><?= $errors['email'] ?? '' ?></span>
        </div>

        <div class="form__item form__item--last<?= isset($errors['password']) ? ' form__item--invalid' : '' ?>">
            <label for="password">Пароль <sup>*</sup></label>
            <input
                id="password"
                type="password"
                name="password"
                placeholder="Введите пароль"
            >
            <span class="form__error"><?= $errors['password'] ?? '' ?></span>
        </div>

        <?php if (isset($errors['auth'])): ?>
            <span class="form__error form__error--bottom">
                <?= $errors['auth'] ?>
            </span>
        <?php endif; ?>

        <button type="submit" class="button">Войти</button>
    </form>
</main>
