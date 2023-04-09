<?php

declare(strict_types=1);

require_once '../vendor/autoload.php';

use Application\Detector\Searcher;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    header('Content-Type: application/json');
    $error = (new Searcher())->checkData($data['host']);
    if (!$error) {
        $values = (new Searcher())->search($data['host']);
        echo json_encode($values);
    } else {
        echo json_encode($error);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IsItBlockedInRussia</title>
    <script src="https://unpkg.com/vue@next"></script>
</head>
<body>
<div id="app">
    <h1>Заблокировано в РФ</h1>
    <input type="text" v-model="inputText" @input="updateGetParams">
    <div v-if="loading" class="loading"></div>
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
            };
        },
        methods: {
            callMyFunction() {
                this.loading = true;
                fetch(`${window.location.protocol}//${window.location.hostname}/webroot/search.php?inputText=${encodeURIComponent(this.inputText)}`)
                    .then(response => response.json())
                    .then(data => {
                        this.loading = false;
                        this.message = data.message;
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
    }

    input[type="text"] {
        width: 100%;
        padding: 10px;
        border: none;
        border-bottom: 2px solid #ddd;
        font-size: 16px;
        outline: none;
    }

    .loading {
        border: 8px solid #f3f3f3;
        border-top: 8px solid #8e24aa;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: 10px auto;
    }

    table {
        margin: 20px;
        border-collapse: collapse;
        width: 100%;
    }

    thead {
        background-color: #8e24aa;
        color: #fff;
    }

    h1 {
        font-size: 36px;
        font-weight: bold;
        text-align: center;
        margin-top: 20px;
        color: #444;
    }

    th,
    td {
        text-align: left;
        padding: 15px;
        border: 1px solid #ddd;
    }

    tbody tr:hover {
        background-color: #fafafa;
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

