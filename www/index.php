<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use Application\Performers\Searcher;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    header('Content-Type: application/json');
    $values = (new Searcher())->search($data['host']);
    echo json_encode($values);
    exit();
}
$data = (new Searcher())->getInfo();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IsItBlockedInRussia</title>
    <script src="https://unpkg.com/vue@next"></script>
</head>
<body>
<div>
    <small><p align="right"> Обновлено в: <?php echo $data[0]['date'];?><br>Заполнено строк: <?php echo $data[0]['allLine'];?></p></small>
</div>
<div id="app">
    <h1>Заблокировано в РФ</h1>
    <input type="text" v-model="inputText" @input="updateGetParams">
    <div v-if="loading" class="loading"></div>
    <div v-if="!loading && site === 'Сайт не заблокирован.'" class="message">{{ site }}</div>
    <table v-if="!loading && message">
        <thead>
        <tr>
            <th>IPv4</th>
            <th>IPv6</th>
            <th>Domain</th>
            <th>URL</th>
            <th>Executive</th>
            <th>Key</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="item in message">
            <td>{{ item['data'][0] }}</td>
            <td>{{ item['data'][1] }}</td>
            <td>{{ item['data'][2] }}</td>
            <td>{{ item['data'][3] }}</td>
            <td>{{ item['data'][4] }}</td>
            <td>{{ item['data'][5] }}</td>
            <td>{{ item['data'][6] }}</td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>
<script>
    const app = Vue.createApp({
        data() {
            return {
                inputText: '',
                loading: false,
                message: '',
                site: '',
            };
        },
        methods: {
            callMyFunction() {
                this.loading = true;
                fetch(`${window.location.protocol}//${window.location.hostname}/search.php?inputText=${encodeURIComponent(this.inputText)}`)
                    .then(response => response.json())
                    .then(data => {
                        this.loading = false;
                        if (data.message[0].responce === false) {
                            this.message = ''
                            this.site = 'Сайт не заблокирован.';
                        } else {
                            this.message = data.message;
                            this.site = '';
                        }
                    })
                    .catch(error => {
                        console.error(error);
                        this.loading = false;
                        this.message = 'Ошибка к подключению.';
                    });
            },
            updateGetParams() {
                clearTimeout(this.timeoutId);
                if (this.inputText === '') {
                    this.message = '';
                    return;
                }
                this.loading = true;
                this.timeoutId = setTimeout(() => {
                    const params = new URLSearchParams(window.location.search);
                    params.set('inputText', this.inputText);
                    const newUrl = `${window.location.pathname}?${params}`;
                    window.history.pushState({path: newUrl}, '', newUrl);
                    this.loading = true;
                    this.callMyFunction();
                }, 1000);
            },
        },
        mounted() {
            const params = new URLSearchParams(window.location.search);
            this.inputText = params.get('inputText') || '';
            if (this.inputText) {
                this.updateGetParams();
            }
        },
    });
    app.mount('#app');
</script>

<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: 'Roboto', sans-serif;
    }

    body {
        background-color: #f5f5f5;
    }

    #app {
        max-width: 1200px;
        margin: 50px auto;
        padding: 30px;
        background-color: #fff;
        box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    input[type="text"] {
        width: 100%;
        padding: 10px;
        border: none;
        border-bottom: 3px solid #ddd;
        font-size: 16px;
        outline: none;
        transition: border-bottom 0.3s ease-in-out;
    }

    input[type="text"] {
        width: 100%;
        height: 40px;
        padding: 10px;
        font-size: 18px;
        border-radius: 20px;
        border-bottom: 3px solid #ffd674;
        outline: none;
    }

    input[type="text"]:focus {
        border-bottom: 3px solid #ce9413;
    }

    .message {
        font-size: 18px;
        font-weight: bold;
        text-align: center;
        margin-top: 20px;
        color: #4CAF50;
    }

    .loading {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 50px;
        margin-top: 20px;
        background-image: url("https://cdnjs.cloudflare.com/ajax/libs/galleriffic/2.0.1/css/loader.gif");
        background-repeat: no-repeat;
        background-position: center;
    }

    table {
        margin: 20px;
        border-collapse: collapse;
        width: 100%;
    }

    th, td {
        text-align: left;
        padding: 10px;
        border: 1px solid #ddd;
    }

    tbody tr:hover {
        background-color: #fafafa;
    }

    thead {
        background-color: #ce9413;
        color: #fff;
    }

    h1 {
        font-size: 36px;
        font-weight: bold;
        text-align: center;
        margin-top: 20px;
        color: #444;
        margin-bottom: 20px;
    }




    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>