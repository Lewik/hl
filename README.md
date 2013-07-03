hl
==

debug class hl
it's one more var_dump

install:
include 'path/to/hl_include_it.php'
or to use in all projects
in php.ini find auto_prepend_file directive and write:
auto_prepend_file = "path/to/hl_include_it.php"
and restart apache

File helpers.php contains shortcut helpers to use hl class.

You can rename hl-class and helpers to avoid conflicts.

Also hl-class writes hl_log.html, so you can debug even you have no access to phps logs


Made by lllewik at gmail dot com
You can do anything with this.

По-русски:
Это класс для отладки

Установка:
include 'path/to/hl_include_it.php'
А еще вы можете сделать так, чтоб этот класс появился "везде":
в php.ini установите (или измените) директиву auto_prepend_file вот так:
auto_prepend_file = "path/to/hl_include_it.php"
и перезапустите апач. Теперь этот файл будет "инклюдиться" всегда перед началом работы со скриптами.

Самим классом hl удобно пользоваться через хелперы из файла helpers.php.

Если есть конфликты преименуйте класс и поправьте хелперы.

Так же класс пишет вывод в файл hl_log.html, через хелпер fhl запись вообще будет только в этот файл - так что можно дебажить при аяксе.
