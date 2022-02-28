<?php
$dark = Request::getCookie('dayNight') == 'dark' ? 'dark' : '';
?>

<!DOCTYPE html>
<html lang="<?= Translate::getLang(); ?>" prefix="og: http://ogp.me/ns# article: http://ogp.me/ns/article# profile: http://ogp.me/ns/profile#">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?= $meta; ?>
  <?php getRequestHead()->output(); ?>
  <link rel="stylesheet" href="/assets/css/style.css?v12">
  <link rel="stylesheet" href="/assets/css/catalog.css?v12">
  <link rel="icon" sizes="16x16" href="/favicon.ico" type="image/x-icon">
  <link rel="icon" sizes="120x120" href="/favicon-120.ico" type="image/x-icon">
</head>

<body <?php if ($dark == 'dark') { ?>class="dark" <?php } ?>>
  <header>
    <div class="page-search">
      <a class="logo black" href="<?= getUrlByName('web'); ?>">
        <?= Translate::get('catalog'); ?>
      </a>
      <div class="page-search-right">
        <div data-template="one" id="find tippy">
          <a class="page-search__url" href="/"><?= Config::get('meta.name'); ?></a>
          <div class="flex right col-span-4 items-center">
            <div id="toggledark" class="header-menu-item mb-none only-icon mr30 mb-ml10">
              <i class="bi bi-brightness-high gray-400 text-xl"></i>
            </div>
            <?php if (!UserData::checkActiveUser()) { ?>
              <?php if (Config::get('general.invite') == false) { ?>
                <a class="register gray-400 mr15 mb-ml10 mb-mr5 block" href="<?= getUrlByName('register'); ?>">
                  <?= Translate::get('registration'); ?>
                </a>
              <?php } ?>
              <a class="gray-400 mr10 ml10" href="<?= getUrlByName('login'); ?>">
                <?= Translate::get('sign.in'); ?>
              </a>
            <?php } else { ?>
              <?php if (UserData::checkAdmin()) { ?>
                <div class="relative mr30 gray-400">
                  <div class="trigger">
                    <?= Translate::get('menu'); ?>
                  </div>
                  <ul class="dropdown">
                    <?= tabs_nav(
                      'menu',
                      'admin',
                      $user,
                      $pages = Config::get('menu.catalog')
                    ); ?>
                  </ul>
                </div>
              <?php } ?>
              <a class="<?php if ($data['sheet'] == 'web.bookmarks') { ?>sky-500 <?php } ?>mr30 green-600" href="<?= getUrlByName('web.bookmarks'); ?>">
                <?= Translate::get('favorites'); ?>
              </a>
              <div class="mr15 m relative">
                <div class="trigger">
                  <?= $user['login']; ?>
                </div>
                <ul class="dropdown">
                  <?= tabs_nav(
                    'menu',
                    'dir',
                    $user,
                    $pages = Config::get('menu.user')
                  ); ?>
                </ul>
              </div>
            <?php } ?>
          </div>
        </div>
        <form method="get" action="<?= getUrlByName('search'); ?>">
          <input type="text" name="q" placeholder="<?= Translate::get('to find'); ?>" class="page-search__input">
          <input name="type" value="website" type="hidden">
        </form>
      </div>
    </div>
  </header>