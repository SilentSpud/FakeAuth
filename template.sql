CREATE TABLE `users` (
  `username` text NOT NULL UNIQUE,
  `password` text NOT NULL,
  `displayName` text NOT NULL,
  `clientToken` text NOT NULL,
  `accessToken` text NOT NULL,
  `skinPath` text NOT NULL,
  `capePath` text NOT NULL
);