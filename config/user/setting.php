<?php
/*
 * Fields on the page: profile setup (Contacts)
 * Поля на странице: настройка профиля (Контакты)
 */

return [
    [
      'url'       => 'website',
      'addition'  => false,
      'title'     => 'website',
      'lang'      => __('app.url'),
      'help'      => 'https://site.ru',
      'name'      => 'website'
    ], [
      'url'       => false,
      'addition'  => false,
      'title'     => 'location',
      'lang'      => __('app.city'),
      'help'      => __('app.for_example') . ': Moscow',
      'name'      => 'location'
    ], [
      'url'       => 'public_email',
      'addition'  => 'mailto:',
      'title'     => 'public_email',
      'lang'      => 'Email',
      'help'      => '**@**.ru',
      'name'      => 'public_email'
    ], [
      'url'       => 'github',
      'addition'  => 'https://github.com/',
      'title'     => 'github',
      'lang'      => 'GitHub',
      'help'      => 'https://github.com/<b>NICK</b>',
      'name'      => 'github'
    ], [
      'url'       => 'skype',
      'addition'  => 'skype:',
      'title'     => 'skype',
      'lang'      => 'Skype',
      'help'      => 'skype:<b>NICK</b>',
      'name'      => 'skype'
    ], [
      'url'       => 'telegram',
      'addition'  => 'tg://resolve?domain=',
      'title'     => 'telegram',
      'lang'      => 'Telegram',
      'help'      => 'tg://resolve?domain=<b>NICK</b>',
      'name'      => 'telegram'
    ], [
      'url'       => 'vk',
      'addition'  => 'https://vk.com/',
      'title'     => 'vk',
      'lang'      => 'Vk',
      'help'      => 'https://vk.com/<b>NICK</b>',
      'name'      => 'vk'
    ],
  ];