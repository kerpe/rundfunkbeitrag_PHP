# PHP formatting

The server code was reformatted once (2026-10-26) from one-line functions into normal PSR-12 layout, without any logic change.

To format again after an edit (needs `composer require friendsofphp/php-cs-fixer nikic/php-parser:^5` in a scratch folder, `vendor/` next to these files):

1. `php brk.php <files>` puts a line break after a `}` that is directly followed by the next statement.
2. `vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php <files>`
3. `php cmp.php <original> <formatted> ...` compares the syntax trees of the old and new files; it prints `same` when only the layout changed.
