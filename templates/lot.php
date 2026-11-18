<?php [$hours, $minutes] = get_dt_range($lot['expiration_date']); ?>
<main>
    <nav class="nav">
        <ul class="nav__list container">
            <?php foreach ($categories as $category): ?>
            <li class="nav__item">
                <a href="all-lots.html"><?= htmlspecialchars($category['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    <section class="lot-item container">
        <h2><?= htmlspecialchars($lot['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h2>
        <div class="lot-item__content">
            <div class="lot-item__left">
                <div class="lot-item__image">
                    <img src="<?= htmlspecialchars($lot['image_path'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" width="730" height="548" alt="">
                </div>
                <p class="lot-item__category">Категория: <span><?= htmlspecialchars($lot['category_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span></p>
                <p class="lot-item__description"><?= htmlspecialchars($lot['description'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            </div>
            <div class="lot-item__right">
                <div class="lot-item__state">
                    <div class="lot-item__timer timer<?= (int) $hours < HOURS_UNTIL_FINISH ? ' timer--finishing' : '' ?>">
                        <?= $hours ?>:<?= $minutes ?>
                    </div>
                    <div class="lot-item__cost-state">
                        <div class="lot-item__rate">
                            <span class="lot-item__amount">Текущая цена</span>
                            <span class="lot-item__cost"><?= htmlspecialchars(to_price((int) $lot['starting_price']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                        </div>
                        <div class="lot-item__min-cost">
                            Мин. ставка <span><?= htmlspecialchars(to_price((int) $lot['starting_price'] + (int) $lot['bid_step']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>