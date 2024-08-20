<?php
$varArray = array (
    "aModulesHistory",
    "aModuleVersions",
    "sUtilModule",
    "aModuleControllers",
    "aDisabledModules",
    "aLegacyModules",
    "aModuleFiles",
    "aModulePaths",
    "aModuleExtensions",
    "aModules",
    "aModuleTemplates",
    "aLanguages",
    "aLanguageParams",
);

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dsn = sprintf('mysql:host=oxid6_mysql;dbname=%s', getenv('MYSQL_DATABASE'));
$username = getenv('MYSQL_USER');
$password = getenv('MYSQL_PASSWORD');

$pdo = new PDO($dsn, $username, $password);

echo '<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        color: #333;
    }
    .container {
        width: 80%;
        margin: 20px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    h2 {
        color: #4CAF50;
        font-size: 24px;
        margin-bottom: 10px;
    }
    textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-family: monospace;
        font-size: 14px;
        box-sizing: border-box;
    }
    button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
    }
    button:hover {
        background-color: #45a049;
    }
    .section {
        margin-bottom: 20px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        background-color: #fafafa;
    }
    .section hr {
        border: none;
        border-top: 1px solid #ddd;
        margin: 20px 0;
    }
    .error {
        color: red;
        font-weight: bold;
        margin-top: 5px;
    }
    .valid {
        color: green;
        font-weight: bold;
        margin-top: 5px;
    }
    .footer {
        text-align: center;
        margin-top: 40px;
        font-size: 12px;
        color: #777;
    }
    .footer a {
        color: #4CAF50;
        text-decoration: none;
    }
</style>';

echo '<div class="container">';
echo "<h2>Bmnnit - OXID eShop Configuration Inspector & Editor</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $varName = $_POST['var_name'];
    $newValue = json_decode($_POST['new_value'], true);

    if (json_last_error() === JSON_ERROR_NONE) {
        $serializedValue = serialize($newValue);
        $stmt = $pdo->prepare("UPDATE oxconfig SET oxvarvalue = ENCODE(?, 'fq45QS09_fqyx09239QQ') WHERE oxvarname = ?");
        $stmt->execute([$serializedValue, $varName]);

        echo "<p style='color: green;'>Updated value for: " . htmlspecialchars($varName) . "</p>";
    } else {
        echo "<p style='color: red;'>Error: Invalid JSON input for $varName.</p>";
    }
}

foreach ($varArray as $var) {
    echo '<div class="section">';
    echo "<h3>Value for: " . htmlspecialchars($var) . "</h3>";
    $stmt = $pdo->prepare("SELECT DECODE(oxvarvalue, 'fq45QS09_fqyx09239QQ') as oxvarvalue FROM oxconfig WHERE oxvarname=?");
    $stmt->execute([$var]);
    $result = $stmt->fetchAll();

    if (count($result)) {
        $currentValue = unserialize($result[0]['oxvarvalue']);
        echo '<form method="POST" onsubmit="return validateJSON(this);">';
        echo '<input type="hidden" name="var_name" value="' . htmlspecialchars($var) . '">';
        echo '<textarea name="new_value" rows="8" oninput="checkJSONSyntax(this)">' . htmlspecialchars(json_encode($currentValue, JSON_PRETTY_PRINT)) . '</textarea>';
        echo '<div class="error" id="error_' . htmlspecialchars($var) . '"></div>';
        echo '<button type="submit">Save</button>';
        echo '</form>';
    } else {
        echo "<p>Empty</p>";
    }

    echo '</div>';
}

echo '<div class="footer">';
echo 'Bmnnit - OXID eShop Configuration Inspector & Editor - <a href="https://bmnnit.com" target="_blank">bmnnit.com</a> | Author: Johannes Baumann | License: <a href="https://en.wikipedia.org/wiki/Beerware" target="_blank">Beerware</a>';
echo '</div>';
echo "</div>";

echo '<script>
function checkJSONSyntax(textarea) {
    const errorDiv = document.getElementById("error_" + textarea.name.split("_")[2]);
    try {
        JSON.parse(textarea.value);
        errorDiv.textContent = "Valid JSON";
        errorDiv.className = "valid";
    } catch (e) {
        errorDiv.textContent = "Invalid JSON: " + e.message;
        errorDiv.className = "error";
    }
}

function validateJSON(form) {
    const textarea = form.querySelector("textarea[name=\'new_value\']");
    const errorDiv = form.querySelector(".error");

    try {
        JSON.parse(textarea.value);
        errorDiv.textContent = "";
        return true;
    } catch (e) {
        errorDiv.textContent = "Invalid JSON: " + e.message;
        return false;
    }
}
</script>';
?>
