<?php
/** @var array $categories */
/** @var array $lot */
/** @var array $errors */

$form_class = $errors ? 'form form--invalid' : 'form';
?>

<main>
    <nav class="nav">
        <ul class="nav__list container">
            <?php foreach ($categories as $category): ?>
            <li class="nav__item">
                <a href="pages/all-lots.html"><?= htmlspecialchars($category['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <form class="<?= $form_class ?> form--add-lot container" action="add.php" method="post" enctype="multipart/form-data">
        <h2>Добавление лота</h2>
        <div class="form__container-two">
            <div class="form__item<?= isset($errors['title']) ? ' form__item--invalid' : '' ?>">
                <label for="lot-name">Наименование <sup>*</sup></label>
                <input id="lot-name" type="text" name="title" placeholder="Введите наименование лота"
                       value="<?= htmlspecialchars($lot['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <span class="form__error"><?= $errors['title'] ?? '' ?></span>
            </div>
            
            <div class="form__item<?= isset($errors['category_id']) ? ' form__item--invalid' : '' ?>">
                <label for="category">Категория <sup>*</sup></label>
                <select id="category" name="category_id">
                    <option value="">Выберите категорию</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= (int) $category['id'] ?>" <?= (string) $lot['category_id'] === (string) $category['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="form__error"><?= $errors['category_id'] ?? '' ?></span>
            </div>
        </div>
        
        <div class="form__item form__item--wide<?= isset($errors['description']) ? ' form__item--invalid' : '' ?>">
            <label for="message">Описание <sup>*</sup></label>
            <textarea id="message" name="description" placeholder="Напишите описание лота"><?= htmlspecialchars($lot['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
            <span class="form__error"><?= $errors['description'] ?? '' ?></span>
        </div>
        
        <div class="form__item form__item--file<?= isset($errors['image']) ? ' form__item--invalid' : '' ?>">
            <label>Изображение <sup>*</sup></label>
            <div class="form__input-file">
                <input class="visually-hidden" type="file" id="lot-img" name="image" value="">
                <label for="lot-img">Добавить</label>
            </div>
            <span class="form__error"><?= $errors['image'] ?? '' ?></span>
        </div>
        
        <div class="form__container-three">
            <div class="form__item form__item--small<?= isset($errors['starting_price']) ? ' form__item--invalid' : '' ?>">
                <label for="lot-rate">Начальная цена <sup>*</sup></label>
                <input id="lot-rate" type="text" name="starting_price" placeholder="0"
                       value="<?= htmlspecialchars($lot['starting_price'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <span class="form__error"><?= $errors['starting_price'] ?? '' ?></span>
            </div>
            
            <div class="form__item form__item--small<?= isset($errors['bid_step']) ? ' form__item--invalid' : '' ?>">
                <label for="lot-step">Шаг ставки <sup>*</sup></label>
                <input id="lot-step" type="text" name="bid_step" placeholder="0"
                       value="<?= htmlspecialchars($lot['bid_step'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <span class="form__error"><?= $errors['bid_step'] ?? '' ?></span>
            </div>
            
            <div class="form__item<?= isset($errors['expiration_date']) ? ' form__item--invalid' : '' ?>">
                <label for="lot-date">Дата окончания торгов <sup>*</sup></label>
                <input class="form__input-date" id="lot-date" type="text" name="expiration_date" placeholder="Введите дату в формате ГГГГ-ММ-ДД"
                       value="<?= htmlspecialchars($lot['expiration_date'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <span class="form__error"><?= $errors['expiration_date'] ?? '' ?></span>
            </div>
        </div>
        
        <?php if ($errors): ?>
            <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
        <?php endif; ?>
        
        <button type="submit" class="button">Добавить лот</button>
    </form>
</main>