# Senku bot 🤖
[![CodeFactor](https://www.codefactor.io/repository/github/mateodioev/senku/badge)](https://www.codefactor.io/repository/github/mateodioev/senku)

Php based telegram bot

## Installation


```bash
git clone https://github.com/Mateodioev/senku
cd senku
composer install
```

- Rename file `example.env` to `.env` and modified data
- Create new table in your db with file db.sql and put your data (Only for bin search)

## Run

### Via long polling
```bash
nohup php index.php &
```
or you can use daemon, see [stack oferflow](https://stackoverflow.com/questions/2036654/run-php-script-as-daemon-process)

### Via webhook
You can use apache or any webserver
First modified `index.php` in line 26

```php
$runner->setCliApp($cli)->setBot($bot)->runWebhook();
```