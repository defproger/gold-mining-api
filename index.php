<?php

function request($method, $url, $data)
{
    $url = "http://localhost/api/$url";
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_URL, $url);
    if ($method === 'POST') {
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        print_r($data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    }
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, 1);
}

if (@$_GET['deleteCompany']) {
    request('DELETE', "companies/{$_GET['deleteCompany']}", []);
    header('Location: index.php');
    exit();
}
if (@$_GET['deleteCountry']) {
    request('DELETE', "countries/{$_GET['deleteCountry']}", []);
    header('Location: index.php');
    exit();
}

if (@$_GET['generateData']) {
    request('POST', 'generate', []);
    header('Location: index.php');
    exit();
}

if ($_POST) {
    if ($_POST['method'] === 'editCompany') {
        request('POST', "companies/edit/{$_POST['id']}", $_POST);
        header('Location: index.php');
        exit();
    }
    if ($_POST['method'] === 'editCountry') {
        request('POST', "countries/edit/{$_POST['id']}", $_POST);
        header('Location: index.php');
        exit();
    }
    if ($_POST['method'] === 'addCompany') {
        request('POST', "companies", $_POST);
        header('Location: index.php');
        exit();
    }
    if ($_POST['method'] === 'addCountry') {
        request('POST', "countries", $_POST);
        header('Location: index.php');
        exit();
    }
}

if (@$_GET['month']) {
    $reports = request('GET', "reports/{$_GET['month']}", []);
}


$countries = request('GET', 'countries/list', []);
$companies = request('GET', 'companies/list', []);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Gold</title>
</head>
<body>
<style>
    * {
        background: black;
        color: green;
        padding: 5px;
        text-decoration: none;
    }

    input {
        margin: 5px;
        padding: 5px;
        border: green 2px solid;
        border-radius: 10px;
    }

    button, a {
        border: none;
        transition: .3s;
    }

    button:hover, a:hover {
        color: darkgreen;
    }

    button.active {
        color: greenyellow;
    }

    .popup {
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
    }
</style>
<div class="tab-headers">
    <button class="tab-header active">[ Companies ]</button>
    <button class="tab-header">[ Leaders ]</button>
</div>
<div class="tabs">
    <div class="tab">
        <div style="display: flex;">
            <div style="width: 50%;">
                <h1>Companies</h1>
                <table>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Country</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($companies as $company): ?>
                        <tr>
                            <td><?= $company['id'] ?></td>
                            <td><?= $company['name'] ?></td>
                            <td><?= $company['country_name'] ?></td>
                            <td><?= $company['email'] ?></td>
                            <td>
                                <button class="popup-1" data-method="company" data-id="<?= $company['id'] ?>">[ edit ]
                                </button>
                                <div id="popup-1" style="display: none;" class="popup">
                                    <div class="popup-content">
                                        <span class="close">&times;</span>
                                        <form action="" method="post">
                                            <input type="hidden" name="method" value="editCompany">
                                            <input type="hidden" name="id" value="<?= $company['id'] ?>">
                                            <input type="text" name="name" value="<?= $company['name'] ?>"
                                                   placeholder="Name" required>
                                            <select name="country_id" required>
                                                <?php foreach ($countries as $country): ?>
                                                    <option value="<?= $country['id'] ?>"><?= $country['name'] ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="email" name="email" value="<?= $company['email'] ?>"
                                                   placeholder="Email" required>
                                            <button type="submit">[ Save ]</button>
                                        </form>
                                    </div>
                                </div>
                                <a href="index.php?deleteCompany=<?= $company['id'] ?>">[ delete ]</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <form action="" method="post">
                    <input type="hidden" name="method" value="addCompany">
                    <input type="text" name="name" placeholder="Name">
                    <select name="country_id" required>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= $country['id'] ?>"><?= $country['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" name="email" placeholder="Email">
                    <button type="submit">[ Add ]</button>
                </form>
            </div>
            <div style="width: 50%;">
                <h1>Countries</h1>
                <table>
                    <tr>
                        <th>Id</th>
                        <th>Name</th>
                        <th>Plan</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($countries as $country): ?>
                        <tr>
                            <td><?= $country['id'] ?></td>
                            <td><?= $country['name'] ?></td>
                            <td><?= $country['plan'] ?></td>
                            <td>
                                <button class="popup-2" data-method="country" data-id="<?= $country['id'] ?>">[ edit ]
                                </button>
                                <div id="popup-2" style="display: none;" class="popup">
                                    <div class="popup-content">
                                        <span class="close">&times;</span>
                                        <form action="" method="post">
                                            <input type="hidden" name="method" value="editCountry">
                                            <input type="hidden" name="id" value="<?= $company['id'] ?>">
                                            <input type="text" name="name" value="<?= $country['name'] ?>"
                                                   placeholder="Name" required>
                                            <input type="number" name="plan" value="<?= $country['plan'] ?>"
                                                   placeholder="Plan" required>
                                            <button type="submit">[ Save ]</button>
                                        </form>
                                    </div>
                                </div>
                                <a href="index.php?deleteCountry=<?= $country['id'] ?>">[ delete ]</a>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <form action="" method="post">
                    <input type="hidden" name="method" value="addCountry">
                    <input type="text" name="name" placeholder="Name">
                    <input type="text" name="plan" placeholder="Plan">
                    <button type="submit">[ Add ]</button>
                </form>
            </div>
        </div>
    </div>
    <div class="tab" style="display: none">
        <h1>Leaders</h1>
        <div>
            <form action="" method="get">
                <select name="month">
                    <option value="1">January</option>
                    <option value="2">February</option>
                    <option value="3">March</option>
                    <option value="4">April</option>
                    <option value="5">May</option>
                    <option value="6">June</option>
                    <option value="7">July</option>
                    <option value="8">August</option>
                    <option value="9">September</option>
                    <option value="10">October</option>
                    <option value="11">November</option>
                    <option value="12">December</option>
                </select>
                <button type="submit">[ Show Report ]</button>
                <a href="index.php?generateData=1">[ Generate data ]</a>
            </form>
            <br>
            <br>
            <br>
            <?php if (!empty($reports)): ?>
                <table>
                    <tr>
                        <th>Id</th>
                        <th>Country</th>
                        <th>Plan</th>
                        <th>Mining</th>
                    </tr>
                    <?php foreach ($reports as $item): ?>
                        <tr>
                            <td><?= $item['id'] ?></td>
                            <td><?= $item['country'] ?></td>
                            <td><?= $item['plan'] ?></td>
                            <td><?= $item['mined'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    let tabHeaders = document.querySelectorAll('.tab-header');
    let tabs = document.querySelectorAll('.tab');

    tabHeaders.forEach((tabHeader, index) => {
        tabHeader.addEventListener('click', () => {
            tabHeaders.forEach(tabHeader => tabHeader.classList.remove('active'));
            tabs.forEach(tab => tab.style.display = 'none');
            tabHeader.classList.add('active');
            tabs[index].style.display = 'block';
        });
    });

    let popups = document.querySelectorAll('.popup');
    let popupButtons = document.querySelectorAll('.popup-1, .popup-2');
    let closeButtons = document.querySelectorAll('.close');

    popupButtons.forEach((popupButton, index) => {
        popupButton.addEventListener('click', () => {
            popups[index].style.display = 'block';
        });
    });

    closeButtons.forEach((closeButton, index) => {
        closeButton.addEventListener('click', () => {
            popups[index].style.display = 'none';
        });
    });
</script>
</body>
</html>