<?php
// Lab 1, Task 4 and extra credit.
// PHP front end -> Python backend (loan.py) -> Plotly chart drawn from the result.
// Author: Md Jonayed Hossain Chowdhury, CPS 5745.

$result = null;
$error  = null;

// Values kept so the form is still filled in after a submit.
$principal = $_POST['principal'] ?? '250000';
$rate      = $_POST['rate']      ?? '6.5';
$years     = $_POST['years']     ?? '30';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // One escapeshellarg per argument. escapeshellcmd on the whole string, as in
    // the lab handout, does not stop a value from being read as a second argument.
    $command = 'python3 ' . escapeshellarg(__DIR__ . '/loan.py')
             . ' ' . escapeshellarg($principal)
             . ' ' . escapeshellarg($rate)
             . ' ' . escapeshellarg($years)
             . ' 2>&1';

    $raw     = shell_exec($command);
    $decoded = json_decode((string) $raw, true);

    if (!is_array($decoded)) {
        $error = 'The Python script did not return readable output: ' . trim((string) $raw);
    } elseif (empty($decoded['ok'])) {
        $error = $decoded['error'] ?? 'The calculation failed.';
    } else {
        $result = $decoded;
    }
}

// Roll the monthly schedule up to years, so the chart has ~30 bars instead of 360.
$years_labels = [];
$interest_by_year = [];
$principal_by_year = [];
if ($result) {
    foreach ($result['schedule'] as $row) {
        $y = (int) ceil($row['month'] / 12);
        $interest_by_year[$y]  = ($interest_by_year[$y]  ?? 0) + $row['interest'];
        $principal_by_year[$y] = ($principal_by_year[$y] ?? 0) + $row['principal'];
    }
    ksort($interest_by_year);
    $years_labels = array_keys($interest_by_year);
    $interest_by_year  = array_map(fn($v) => round($v, 2), array_values($interest_by_year));
    $principal_by_year = array_map(fn($v) => round($v, 2), array_values($principal_by_year));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Loan Payment Calculator</title>
<!-- Plotly from the CDN, falling back to the copy sitting in this same folder
     if the CDN is blocked on the network the page is opened from. -->
<script src="https://cdn.plot.ly/plotly-2.35.2.min.js" charset="utf-8"></script>
<script>
  if (!window.Plotly) {
    document.write('<script src="plotly.min.js" charset="utf-8"><\/script>');
  }
</script>
<style>
  :root {
    --surface-1: #fcfcfb;
    --text-primary: #0b0b0b;
    --text-secondary: #52514e;
    --series-principal: #2a78d6;
    --series-interest: #eb6834;
    --rule: #dfe2e6;
  }
  body { font-family: -apple-system, "Segoe UI", Helvetica, Arial, sans-serif;
         background: var(--surface-1); color: var(--text-primary);
         max-width: 900px; margin: 36px auto; padding: 0 20px; line-height: 1.55; }
  h1 { font-size: 24px; margin: 0 0 4px; }
  .lede { color: var(--text-secondary); margin: 0 0 4px; }
  .byline { color: var(--text-secondary); font-size: 14px; margin: 0 0 24px; }
  form { display: flex; gap: 18px; flex-wrap: wrap; align-items: flex-end;
         padding: 16px; border: 1px solid var(--rule); border-radius: 6px; }
  .field { display: flex; flex-direction: column; }
  .field label { font-size: 13px; color: var(--text-secondary); margin-bottom: 4px; }
  .field input { padding: 7px 9px; border: 1px solid #c9ccd1; border-radius: 4px;
                 font-size: 15px; width: 150px; }
  button { padding: 9px 20px; border: 0; border-radius: 4px; background: #1f4e79;
           color: #fff; font-size: 15px; cursor: pointer; }
  .stats { display: flex; gap: 28px; flex-wrap: wrap; margin: 26px 0 8px; }
  .stat .k { font-size: 13px; color: var(--text-secondary); }
  .stat .v { font-size: 26px; font-weight: 600; letter-spacing: -0.01em; }
  .err { margin-top: 20px; padding: 12px 14px; border-left: 3px solid #9c2b2b;
         background: #fdf3f3; }
  table { border-collapse: collapse; width: 100%; margin-top: 10px; font-size: 14px; }
  th, td { text-align: right; padding: 6px 8px; border-bottom: 1px solid var(--rule); }
  th:first-child, td:first-child { text-align: left; }
  th { color: var(--text-secondary); font-weight: 600; font-size: 13px; }
  details { margin-top: 26px; }
  summary { cursor: pointer; color: var(--text-secondary); font-size: 14px; }
  .note { color: var(--text-secondary); font-size: 13px; margin-top: 8px; }
</style>
</head>
<body>

<h1>Loan Payment Calculator</h1>
<p class="lede">
  What a fixed-rate loan costs per month, and how much of each year's payments is
  interest rather than repayment. The amortised payment formula is
  M&nbsp;=&nbsp;P&nbsp;&times;&nbsp;i&nbsp;&divide;&nbsp;(1&nbsp;&minus;&nbsp;(1&nbsp;+&nbsp;i)<sup>&minus;n</sup>),
  where P is the amount borrowed, i is the monthly interest rate and n is the number of payments.
</p>
<p class="byline">
  Author: Md Jonayed Hossain Chowdhury &middot; CPS 5745 Lab 1 &middot;
  <?php echo date('F j, Y'); ?>
</p>

<form method="POST" action="">
  <div class="field">
    <label for="principal">Amount borrowed (USD)</label>
    <input type="text" id="principal" name="principal" value="<?php echo htmlspecialchars($principal); ?>">
  </div>
  <div class="field">
    <label for="rate">Annual interest rate (%)</label>
    <input type="text" id="rate" name="rate" value="<?php echo htmlspecialchars($rate); ?>">
  </div>
  <div class="field">
    <label for="years">Term (years)</label>
    <input type="text" id="years" name="years" value="<?php echo htmlspecialchars($years); ?>">
  </div>
  <button type="submit" name="submit">Calculate</button>
</form>

<?php if ($error): ?>
  <div class="err"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($result): ?>
  <div class="stats">
    <div class="stat">
      <div class="k">Monthly payment</div>
      <div class="v">$<?php echo number_format($result['monthly_payment'], 2); ?></div>
    </div>
    <div class="stat">
      <div class="k">Total interest</div>
      <div class="v">$<?php echo number_format($result['total_interest'], 2); ?></div>
    </div>
    <div class="stat">
      <div class="k">Total repaid</div>
      <div class="v">$<?php echo number_format($result['total_repaid'], 2); ?></div>
    </div>
    <div class="stat">
      <div class="k">Interest share of total</div>
      <div class="v"><?php echo $result['interest_share_percent']; ?>%</div>
    </div>
  </div>

  <div id="chart" style="height:420px;"></div>
  <p class="note">
    Every bar is computed from the numbers you entered: Python returns the full
    <?php echo $result['payments']; ?>-month schedule and the page groups it by year.
  </p>

  <details>
    <summary>Show the first 12 months of the schedule as a table</summary>
    <table>
      <thead>
        <tr><th>Month</th><th>Interest</th><th>Principal</th><th>Balance</th></tr>
      </thead>
      <tbody>
      <?php foreach (array_slice($result['schedule'], 0, 12) as $row): ?>
        <tr>
          <td><?php echo $row['month']; ?></td>
          <td>$<?php echo number_format($row['interest'], 2); ?></td>
          <td>$<?php echo number_format($row['principal'], 2); ?></td>
          <td>$<?php echo number_format($row['balance'], 2); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </details>

  <script>
  const years     = <?php echo json_encode($years_labels); ?>;
  const interest  = <?php echo json_encode($interest_by_year); ?>;
  const principal = <?php echo json_encode($principal_by_year); ?>;
  const surface   = '#fcfcfb';

  const traces = [
    {
      type: 'bar', name: 'Interest', x: years, y: interest,
      marker: { color: '#eb6834', line: { color: surface, width: 1 } },
      hovertemplate: 'Year %{x}<br>Interest $%{y:,.0f}<extra></extra>'
    },
    {
      type: 'bar', name: 'Principal repaid', x: years, y: principal,
      marker: { color: '#2a78d6', line: { color: surface, width: 1 } },
      hovertemplate: 'Year %{x}<br>Principal $%{y:,.0f}<extra></extra>'
    }
  ];

  Plotly.newPlot('chart', traces, {
    barmode: 'stack',
    bargap: 0.25,
    title: {
      text: 'Where each year of payments goes',
      x: 0, xanchor: 'left', font: { size: 16, color: '#0b0b0b' }
    },
    paper_bgcolor: surface,
    plot_bgcolor: surface,
    font: { family: '-apple-system, Segoe UI, Helvetica, Arial, sans-serif',
            color: '#52514e', size: 13 },
    xaxis: { title: { text: 'Year of the loan' }, showgrid: false,
             linecolor: '#dfe2e6', ticks: 'outside', tickcolor: '#dfe2e6' },
    yaxis: { title: { text: 'Paid during the year (USD)' },
             gridcolor: '#eef0f2', zerolinecolor: '#dfe2e6', tickprefix: '$',
             separatethousands: true },
    legend: { orientation: 'h', y: 1.08, x: 0, xanchor: 'left' },
    hovermode: 'x unified',
    margin: { l: 80, r: 20, t: 70, b: 55 }
  }, { displayModeBar: false, responsive: true });
  </script>
<?php endif; ?>

</body>
</html>
