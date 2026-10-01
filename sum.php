<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PHP to Python</title>
    <style>
        body { font-family: -apple-system, "Segoe UI", Helvetica, Arial, sans-serif;
               max-width: 640px; margin: 40px auto; padding: 0 20px; color: #1b2430;
               line-height: 1.5; }
        h1 { font-size: 22px; margin-bottom: 2px; }
        .byline { color: #5b6470; font-size: 14px; margin-bottom: 24px; }
        label { display: inline-block; width: 90px; }
        input[type=text] { padding: 6px 8px; border: 1px solid #c9ccd1; border-radius: 4px; }
        input[type=submit] { margin-top: 12px; padding: 7px 16px; border: 0;
                             border-radius: 4px; background: #1f4e79; color: #fff;
                             font-size: 14px; cursor: pointer; }
        .result { margin-top: 24px; padding: 12px 16px; background: #f1f5f9;
                  border-left: 3px solid #1f4e79; border-radius: 3px; }
        .result h3 { margin: 0 0 6px; font-size: 15px; color: #1f4e79; }
        .result p { margin: 0; }
    </style>
</head>
<body>
    <h1>Sum App: PHP front end calling a Python script</h1>
    <p class="byline">
        Md Jonayed Hossain Chowdhury &middot; CPS 5745 Lab 1 &middot;
        <?php echo date("F j, Y"); ?>
    </p>

    <form method="POST" action="">
        <label for="num1">Number 1:</label>
        <input type="text" id="num1" name="num1"
               value="<?php echo isset($_POST['num1']) ? htmlspecialchars($_POST['num1']) : ''; ?>">
        <br><br>
        <label for="num2">Number 2:</label>
        <input type="text" id="num2" name="num2"
               value="<?php echo isset($_POST['num2']) ? htmlspecialchars($_POST['num2']) : ''; ?>">
        <br>
        <input type="submit" name="submit" value="Calculate">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $num1 = $_POST["num1"];
        $num2 = $_POST["num2"];

        // Build the command one argument at a time. escapeshellarg quotes each
        // value separately, so a value such as  3; rm -rf ~  is passed to Python
        // as text rather than being run by the shell. escapeshellcmd on the whole
        // string, as in the lab handout, does not give that guarantee.
        $command = "python3 " . escapeshellarg(__DIR__ . "/sum.py")
                 . " " . escapeshellarg($num1)
                 . " " . escapeshellarg($num2);

        // Execute the Python script and capture the output
        $output = shell_exec($command . " 2>&1");

        // Display the result
        echo '<div class="result">';
        echo "<h3>Result from Python script:</h3>";
        echo "<p>" . htmlspecialchars(trim((string) $output)) . "</p>";
        echo '</div>';
    }
    ?>
</body>
</html>
