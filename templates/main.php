
<main class="container">
    <section class="promo">
        <h2 class="promo__title">Нужен стафф для катки?</h2>
        <p class="promo__text">На нашем интернет-аукционе ты найдёшь самое эксклюзивное сноубордическое и горнолыжное снаряжение.</p>
        <ul class="promo__list">
            <?php foreach ($categories as $category): ?>
            <li class="promo__item">
                <a class="promo__link" href="/index.php?category_id=<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
            </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <section class="lots">
        <div class="lots__header">
            <h2>Открытые лоты</h2>
        </div>
        <ul class="lots__list">
            <?php foreach ($lots as $lot): ?>
            <?php [$hours, $minutes] = get_dt_range($lot['expiration_date']); ?>
            <li class="lots__item lot">
                <div class="lot__image">
                    <img src="<?= htmlspecialchars($lot['image_path'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" width="350" height="260" alt=""> <!--ENT_QUOTES и ENT_SUBSTITUTE флаги настроек для экранирования-->
                </div>
                <div class="lot__info">
                    <span class="lot__category"><?= htmlspecialchars($lot['category_name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                    <h3 class="lot__title"><a class="text-link" href="lot.php?id=<?= (int) $lot['id'] ?>"><?= htmlspecialchars($lot['title'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a></h3>
                    <div class="lot__state">
                        <div class="lot__rate">
                            <span class="lot__amount">Стартовая цена</span>
                            <span class="lot__cost"><?= htmlspecialchars(to_price((int) $lot['starting_price']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                        </div>
                        <div class="lot__timer timer<?= (int) $hours < HOURS_UNTIL_FINISH ? ' timer--finishing' : '' ?>">
                            <?= $hours ?>:<?= $minutes ?>
                        </div>
                    </div>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </section>
</main>
