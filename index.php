<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kaspa Transactions</title>
</head>
<body>
  <h1>Kaspa Transactions</h1>
  <form>
    <label for="kaspa-address">Kaspa Address:</label>
    <input type="text" id="kaspa-address" name="kaspa-address" value="kaspa:xxxxxx"><br><br>
    <label for="limit">Limit:</label>
    <input type="number" id="limit" name="limit" min="1" max="500" value="30"><br><br>
     <label for="offset">Offset:</label>
    <input type="text" id="offset" name="offset" value="0"><br><br>
    <label for="rate">Kaspa/$ rate:</label>
    <input type="text" id="rate" name="rate" value="0.0076"><br><br>
  
    <input type="submit" value="Submit" id="submit-btn">
  </form>
  <br>
  <div id="results"></div>
  <table id="transaction-table">
    <thead>
      <tr>
        <th>Date</th>
        <th>Amount (KAS)</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>

  <script>
    const form = document.querySelector('form');
    const results = document.getElementById('results');
    const tableBody = document.querySelector('tbody');
    form.addEventListener('submit', e => {
      e.preventDefault();
      const kaspaAddress = document.getElementById('kaspa-address').value;
      const limit = document.getElementById('limit').value;
      const rate = document.getElementById('rate').value;
      const offset = document.getElementById('offset').value;
      results.innerHTML = '<p>Loading...</p>';
      tableBody.innerHTML = '';
      const xhr = new XMLHttpRequest();
      xhr.open('GET', `https://api.kaspa.org/addresses/${kaspaAddress}/full-transactions?limit=${limit}&offset=${offset}`, true);
      xhr.onload = function() {
        if (this.status === 200) {
          const decoded_data = JSON.parse(this.responseText);
          let firstTime = 0;
          let totalAmount = 0;
          let lastAmount = 0;
          for (const transaction of decoded_data) {
            for (const output of transaction.outputs) {
              if (output.script_public_key_address === kaspaAddress) {
                if (firstTime === 0) {
                  firstTime = transaction.block_time;
                }
                lastAmount = output.amount / 100000000;
                totalAmount += lastAmount;
                const dateFormatted = new Date(transaction.block_time).toLocaleString();
                tableBody.innerHTML += `
                  <tr>
                    <td>${dateFormatted}</td>
                    <td>${lastAmount.toFixed(8)}</td>
                  </tr>
                `;
              }
            }
          }
          totalAmount -= lastAmount;
          const totalSecs = (firstTime - decoded_data[decoded_data.length - 1].block_time) / 1000;
          const totalDays = totalSecs / 3600 / 24;
          const dailyEarnings = (totalAmount / totalSecs * 3600 * 24 * rate).toFixed(2);
          results.innerHTML = `
            <p>Total Time: ${totalDays.toFixed(2)} days (${totalSecs.toFixed(2)} seconds)</p>
            <p>Total Amount: ${totalAmount.toFixed(8)} KAS</p>
            <p>Daily Earnings: $${dailyEarnings}</p>
          `;
        } else {
          results.innerHTML = '<p>Error loading data.</p>';
        }
      };
      xhr.send();
    });
  </script>
</body>
</html>
