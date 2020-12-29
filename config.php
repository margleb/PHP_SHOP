<?php

# если константа не определена, то доступ запрещен
defined('VG_ACCESS') or die('Access denied');

const SITE_URL = 'http//im.my'; # ссылка сайта
const PATH = '/'; # корень пути сайта

# DB
const HOST = 'localhist';
const USER = 'root';
const PASS = '';
const DB_NAME = 'im';

// echo 'hello';