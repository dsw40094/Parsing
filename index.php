<?php
function normalizeText(string $text): string
{
    $text = trim($text);
    return preg_replace('/\s+/', ' ', $text);
}

function parseSentences(string $text): array
{
    $sentences = preg_split('/(?<=[.!?])\s+/', trim($text));
    $sentences = array_filter(array_map('trim', $sentences));
    return array_values($sentences);
}

function parseWords(string $text): array
{
    preg_match_all('/[\p{L}\p{N}\']+/u', mb_strtolower($text), $matches);
    return $matches[0] ?? [];
}

function parseCsv(string $text): array
{
    $rows = array_filter(array_map('trim', explode("\n", trim($text))));
    $result = [];
    foreach ($rows as $row) {
        $result[] = str_getcsv($row);
    }
    return $result;
}

$defaultText = "Parsing is the process of analyzing text.\nIt helps us understand structure and meaning!";
$sourceText = $_POST['source_text'] ?? $defaultText;
$parserType = $_POST['parser'] ?? 'sentences';
$results = [];
$summary = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cleanText = normalizeText($sourceText);

    switch ($parserType) {
        case 'words':
            $results = parseWords($cleanText);
            $summary = 'Wörter insgesamt: ' . count($results);
            break;
        case 'csv':
            $results = parseCsv($sourceText);
            $summary = 'CSV-Zeilen: ' . count($results);
            break;
        default:
            $results = parseSentences($cleanText);
            $summary = 'Sätze gefunden: ' . count($results);
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>HHU WLAN Parsing Portal</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Helvetica Neue', Arial, 'Noto Sans KR', sans-serif;
            background: #1f2a3d;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem 1rem;
        }

        .card {
            width: min(420px, 100%);
            text-align: center;
            background: linear-gradient(180deg, #5bb8ff 0%, #4f90d9 100%);
            border-radius: 16px;
            padding: 2rem 2.5rem 2.2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
            color: white;
        }

        .logo {
            font-size: clamp(3.2rem, 18vw, 4.3rem);
            font-weight: 700;
            text-transform: lowercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.75rem;
        }

        .subtitle {
            font-size: 1.05rem;
            line-height: 1.4;
            margin-bottom: 1.2rem;
        }

        .warning {
            background: rgba(0, 0, 0, 0.18);
            border-radius: 8px;
            padding: 0.85rem;
            font-size: 0.9rem;
            line-height: 1.4;
            margin-bottom: 1.4rem;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
        }

        label {
            text-align: left;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            font-weight: 700;
        }

        textarea,
        select {
            width: 100%;
            border: none;
            border-radius: 6px;
            padding: 0.75rem 0.8rem;
            font-family: inherit;
            font-size: 0.95rem;
            color: #0a2a4d;
        }

        textarea {
            min-height: 88px;
            resize: vertical;
        }

        .checkbox-line {
            display: flex;
            gap: 0.55rem;
            font-size: 0.85rem;
            text-align: left;
            align-items: flex-start;
        }

        button {
            border: none;
            background: #f28705;
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 0.9rem;
            border-radius: 999px;
            cursor: pointer;
            margin-top: 0.5rem;
        }

        .results {
            width: min(420px, 100%);
            margin: 1.25rem auto 0;
            background: white;
            border-radius: 12px;
            padding: 1.25rem;
            color: #0f2d4a;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        }

        .summary {
            font-weight: 700;
            margin-bottom: 0.6rem;
        }

        .chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
        }

        .chip {
            background: #e6f0ff;
            border-radius: 999px;
            padding: 0.3rem 0.6rem;
            font-size: 0.85rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid rgba(15, 45, 74, 0.2);
            padding: 0.45rem 0.5rem;
            text-align: left;
            font-size: 0.85rem;
        }

        th {
            background: #f4f7fb;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.7rem;
        }

        @media (max-width: 460px) {
            .card {
                padding: 1.8rem;
            }
        }
    </style>
</head>
<body>
<div>
    <div class="card">
        <div class="logo">hhu</div>
        <div class="subtitle">
            Willkommen an der Heinrich Heine Universität Düsseldorf
        </div>
        <div class="warning">
            Dieser WLAN Zugang ist nicht sicher, bitte nutzen Sie eduroam. Schalten Sie eigene WLAN Hotspots aus.
        </div>
        <form method="post">
            <div>
                <label for="source_text">Benutzername</label>
                <textarea id="source_text" name="source_text" required><?= htmlspecialchars($sourceText) ?></textarea>
            </div>
            <div>
                <label for="parser">Passwort</label>
                <select id="parser" name="parser">
                    <option value="sentences" <?= $parserType === 'sentences' ? 'selected' : '' ?>>Sätze analysieren</option>
                    <option value="words" <?= $parserType === 'words' ? 'selected' : '' ?>>Wörter zählen</option>
                    <option value="csv" <?= $parserType === 'csv' ? 'selected' : '' ?>>CSV zerlegen</option>
                </select>
            </div>
            <label class="checkbox-line">
                <input type="checkbox" checked disabled>
                <span>Ich akzeptiere die Datenschutzbestimmungen</span>
            </label>
            <button type="submit">Anmelden</button>
        </form>
    </div>

    <div class="results">
        <h2>Analyse</h2>
        <?php if ($summary): ?>
            <div class="summary"><?= htmlspecialchars($summary) ?></div>
        <?php else: ?>
            <p>Bitte Text eingeben und Anmelden klicken.</p>
        <?php endif; ?>

        <?php if ($results): ?>
            <?php if ($parserType === 'csv'): ?>
                <table>
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Werte</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($results as $rowIndex => $row): ?>
                        <tr>
                            <td><?= $rowIndex + 1 ?></td>
                            <td><?= htmlspecialchars(implode(' | ', $row)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="chip-list">
                    <?php foreach ($results as $token): ?>
                        <span class="chip"><?= htmlspecialchars($token) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
