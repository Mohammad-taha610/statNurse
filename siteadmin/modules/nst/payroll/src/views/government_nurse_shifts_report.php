<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 32px;
        }

        .header img {
            width: 40%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="<?= 'data:image/png;base64,' . base64_encode(file_get_contents($logoFilePath))?>" alt="Logo">
        <h1><?= $nurseName ?> Shifts Worked Report <?= $yearAndQuarter ?></h1>
    </div>
    <table>
        <thead>
            <tr>
                <?php foreach ($columnHeaders as $header) { ?>
                    <td><?=$header?></td>
                <?php } ?>
            </tr>
        </thead>
        <tbody>

            <?php foreach ($shiftData as $sData) { ?>
                <tr>
                    <td><?= $sData['date'] ?></td>
                    <td><?= $sData['location'] ?></td>
                    <td><?= $sData['hoursTotal'] ?></td>
                    <td><?= $sData['payTotal'] ?></td>
                    <td><?= $sData['billTotal'] ?></td>
                    <td><?= $sData['credentials'] ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</body>

</html>