-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Хост: mysql
-- Время создания: Ноя 17 2021 г., 09:39
-- Версия сервера: 5.7.35
-- Версия PHP: 7.4.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `messanger_db`
--

-- --------------------------------------------------------

--
-- Структура таблицы `chat_rooms`
--

CREATE TABLE `chat_rooms` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `chat_rooms`
--

INSERT INTO `chat_rooms` (`id`, `title`, `created_at`, `updated_at`) VALUES
(1, 'Чат комната 1', '2021-11-17 11:08:23', NULL),
(2, 'Чат комната 2', '2021-11-17 11:08:50', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `messages`
--

CREATE TABLE `messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `sender_id` int(10) UNSIGNED NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `audio` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chat_room_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `message`, `audio`, `chat_room_id`, `created_at`, `updated_at`) VALUES
(3, 2, 'Привет', NULL, 1, '2021-11-17 08:22:34', '2021-11-17 08:22:34'),
(4, 2, 'Как дела ?', NULL, 1, '2021-11-17 08:22:51', '2021-11-17 08:22:51'),
(5, 1, 'Привет,все работает!', NULL, 1, '2021-11-17 08:23:37', '2021-11-17 08:23:37'),
(6, 1, NULL, 'voicemessages/voice_17:11:2021 08:24:30.arm', 1, '2021-11-17 08:24:30', '2021-11-17 08:24:30'),
(7, 1, 'Отлично', NULL, 1, '2021-11-17 08:31:52', '2021-11-17 08:31:52');

-- --------------------------------------------------------

--
-- Структура таблицы `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(3, '2021_09_09_123842_create_users_friends_table', 1),
(4, '2021_09_09_124442_create_chat_rooms_table', 1),
(5, '2021_09_09_124803_create_users_chat_rooms', 1),
(6, '2021_09_09_125050_create_messages_table', 1),
(9, '2021_11_10_065744_alter_messages_table', 2),
(10, '2021_11_17_081800_alter_user_table', 2);

-- --------------------------------------------------------

--
-- Структура таблицы `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 2, 'usertoken', '6c671e7128866667895bde14ba46b95c6aa24b346d3cc54a1c58b02a60b049df', '[\"*\"]', '2021-11-17 08:27:56', '2021-11-17 08:08:12', '2021-11-17 08:27:56'),
(2, 'App\\Models\\User', 2, 'usertoken', '9743040f4c825f73300daf089f268281d00d6585cbb68f65453d6ffe1f418afc', '[\"*\"]', '2021-11-17 08:28:16', '2021-11-17 08:11:32', '2021-11-17 08:28:16'),
(3, 'App\\Models\\User', 2, 'usertoken', '0acf5b8b05aa4d602e687f94cba9a3035579001e2688d86b5a6aaab7e12e4a97', '[\"*\"]', '2021-11-17 08:12:20', '2021-11-17 08:11:45', '2021-11-17 08:12:20'),
(4, 'App\\Models\\User', 1, 'usertoken', '997384e2cd7e79d13b37fdabe886ae5d8c6ecd7b5cc1af1175d8bb609c12d4a6', '[\"*\"]', '2021-11-17 08:25:11', '2021-11-17 08:23:14', '2021-11-17 08:25:11'),
(5, 'App\\Models\\User', 1, 'usertoken', '8047e0d6a6028f158ab5e511fe286e5e1dcc007b27ec72d236085f495a16b87e', '[\"*\"]', '2021-11-17 08:26:53', '2021-11-17 08:26:47', '2021-11-17 08:26:53'),
(6, 'App\\Models\\User', 1, 'usertoken', '2a878ed5885ae643040a771374468063c075c73c6c2ecefb215d8ec1d7862244', '[\"*\"]', '2021-11-17 08:32:32', '2021-11-17 08:27:05', '2021-11-17 08:32:32');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `socket_id` int(11) DEFAULT NULL,
  `username` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `number` int(11) DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(4) NOT NULL,
  `last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `socket_id`, `username`, `email`, `password`, `number`, `avatar`, `active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 748, 'Черный пистолет', 'test@ex.com', '$2y$10$7w0UTnxgy6p2sBJh/CO6DOwUPaZdmcVTTfuLPH/e8SuVGhBiYRYoa', NULL, NULL, 1, '2021-11-17 08:32:30', '2021-11-17 08:07:17', '2021-11-17 08:07:17'),
(2, 707, 'Данил Струченков', 'test2@ex.com', '$2y$10$gYUXdfthR52.2BujUzVDfeSuVqnzwOvbo65i.OS5YpUqzhAKaqQ1q', NULL, NULL, 1, '2021-11-17 08:22:55', '2021-11-17 08:07:42', '2021-11-17 08:07:42'),
(3, NULL, 'Андрей Двойкин', 'test3@ex.com', '$2y$10$Uc7.koSeaWByYJ7QNjVpd.6mrz64TT.zNAJzzUZaHPLX4eEv2IIIe', NULL, NULL, 1, '2021-11-17 08:08:03', '2021-11-17 08:08:03', '2021-11-17 08:08:03');

-- --------------------------------------------------------

--
-- Структура таблицы `users_chat_rooms`
--

CREATE TABLE `users_chat_rooms` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `chat_room_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users_chat_rooms`
--

INSERT INTO `users_chat_rooms` (`id`, `user_id`, `chat_room_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2021-11-17 11:09:08', NULL),
(2, 2, 1, '2021-11-17 11:09:24', NULL),
(3, 3, 2, '2021-11-17 11:09:42', NULL),
(4, 2, 2, '2021-11-17 11:10:04', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `users_friends`
--

CREATE TABLE `users_friends` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `friend_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users_friends`
--

INSERT INTO `users_friends` (`id`, `user_id`, `friend_id`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2021-11-17 11:10:23', NULL),
(2, 2, 1, '2021-11-17 11:10:40', NULL);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `chat_rooms`
--
ALTER TABLE `chat_rooms`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_sender_id_foreign` (`sender_id`),
  ADD KEY `messages_chat_room_id_foreign` (`chat_room_id`);

--
-- Индексы таблицы `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_number_unique` (`number`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Индексы таблицы `users_chat_rooms`
--
ALTER TABLE `users_chat_rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_chat_rooms_user_id_foreign` (`user_id`),
  ADD KEY `users_chat_rooms_chat_room_id_foreign` (`chat_room_id`);

--
-- Индексы таблицы `users_friends`
--
ALTER TABLE `users_friends`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_friends_user_id_foreign` (`user_id`),
  ADD KEY `users_friends_friend_id_foreign` (`friend_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `chat_rooms`
--
ALTER TABLE `chat_rooms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `users_chat_rooms`
--
ALTER TABLE `users_chat_rooms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `users_friends`
--
ALTER TABLE `users_friends`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_chat_room_id_foreign` FOREIGN KEY (`chat_room_id`) REFERENCES `chat_rooms` (`id`),
  ADD CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `users_chat_rooms`
--
ALTER TABLE `users_chat_rooms`
  ADD CONSTRAINT `users_chat_rooms_chat_room_id_foreign` FOREIGN KEY (`chat_room_id`) REFERENCES `chat_rooms` (`id`),
  ADD CONSTRAINT `users_chat_rooms_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `users_friends`
--
ALTER TABLE `users_friends`
  ADD CONSTRAINT `users_friends_friend_id_foreign` FOREIGN KEY (`friend_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `users_friends_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
