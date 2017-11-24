<?php
include('Net/SSH2.php');

// connect to pi e.g. mypi.ddns.net or 192.168.0.10
$ssh = new Net_SSH2('my_pi_IP_or_URL');
// user and password
if (!$ssh->login('user', 'password')) {
    exit('Login Failed');
}

$gethelp = $ssh->exec('deeponiond help');
$help = htmlspecialchars($gethelp);

$getinfo = $ssh->exec('deeponiond getinfo');
$info = json_decode($getinfo);

$getstakinginfo = $ssh->exec('deeponiond getstakinginfo');
$staking = json_decode($getstakinginfo);

$expectedtime = $staking->expectedtime;
$hours = floor($expectedtime / 3600);
$minutes = floor(($expectedtime / 60) % 60);
$expected_time = $hours.' hours '.$minutes.' minutes';

$listaccounts = $ssh->exec('deeponiond listaccounts');
$accounts = json_decode($listaccounts);

// get pi temperature
$temp = $ssh->exec('vcgencmd measure_temp');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
  <style>
    body {
      font-family: 'Roboto', sans-serif;
    }
    .small-nav {
      height: 18px;
      line-height: 18px;
      margin-bottom: 20px;
    }
    nav .brand-logo {
      font-size: 3vh;
    }
  </style>
  <title>My DeepOnion Stats</title>
</head>
<body>
  <nav class="blue-grey darken-3">
    <div class="nav-wrapper">
      <div class="container">
        <a href="/" class="brand-logo">My DeepOnion Stats</a>
        <a href="#" data-activates="mobile-demo" class="button-collapse"><i class="material-icons">menu</i></a>
        <ul class="right hide-on-med-and-down">
          <li><?php echo $temp;?></li>
          <li><a class="waves-effect waves-light btn modal-trigger amber accent-4" href="#helpModal">Help</a></li>
        </ul>
        <ul class="blue-grey darken-3 side-nav" id="mobile-demo">
          <li class="center-align"><?php echo $temp;?></li>
          <li><a class="waves-effect waves-light btn modal-trigger amber accent-4" href="#helpModal">Help</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <nav class="small-nav green"></nav>
  <div class="container">
    <div class="row">
      <div class="col s12 m12 l12 xl6">
        <div class="card">
          <div class="card-content">
            <p class="card-title">Main info</p><br/>
            <ul class="collection">
              <li class="collection-item"><b>Balance: </b><?php echo $info->balance; ?></li>
              <li class="collection-item"><b>Blocks: </b><?php echo number_format($info->blocks); ?></li>
              <li class="collection-item"><b>Money supply: </b><?php echo number_format($info->moneysupply); ?></li>
              <li class="collection-item"><b>Connections: </b><?php echo $info->connections; ?></li>
              <li class="collection-item"><b>IP: </b><?php echo $info->ip; ?></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="col s12 m12 l12 xl6">
        <div class="card">
          <div class="card-content">
            <p class="card-title">Staking info</p><br/>
            <ul class="collection">
              <li class="collection-item"><b>Expected time: </b><?php echo $expected_time; ?></li>
              <li class="collection-item"><b>Weight: </b><?php echo number_format($staking->weight); ?></li>
              <li class="collection-item"><b>Network stake weight: </b><?php echo number_format($staking->netstakeweight); ?></li>
              <li class="collection-item"><b>Difficulty: </b><?php echo $staking->difficulty; ?></li>
              <li class="collection-item"><b>Current block size: </b><?php echo $staking->currentblocksize; ?></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="col s12">
        <div class="card">
          <div class="card-content">
            <p class="card-title">Transactions</p><br/>
            <table class="striped bordered highlight responsive-table">
              <thead>
                <tr>
                  <th>Account</th>
                  <th>Address</th>
                  <th>Type</th>
                  <th>Amount</th>
                  <th>Time</th>
                </tr>
              </thead>
              <tbody>
              <?php
                foreach ($accounts as $akey => $avalue) {
                  // get last 100 transactions
                  $listtransactions = $ssh->exec('deeponiond listtransactions "'.$akey.'" 100');
                  $transactions = json_decode($listtransactions);
                  foreach (array_reverse($transactions) as $transaction) {
                    echo '<tr>';
                    foreach ($transaction as $kt => $vt) {
                      switch ($kt) {
                        case 'account':
                        case 'address':
                        case 'amount':
                          echo '<td>'.$vt.'</td>';
                          break;
                        case 'category':
                          echo '<td>';
                          // display 'stake' instead of 'generate'
                          echo $vt == 'generate' ? 'stake' : $vt;
                          echo '</td>';
                          break;
                        case 'time':
                          echo '<td>'.date('d.m.Y H:i:s',$vt).'</td>';
                          break;
                        default:
                          break;
                      }
                    }
                    echo '</tr>';
                  }
                }
              ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div id="helpModal" class="modal bottom-sheet">
    <div class="modal-content">
      <h4>Help</h4>
      <p><pre><?php echo $help;?></pre></p>
    </div>
  </div>
  <script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/js/materialize.min.js"></script>
  <script>
    $(document).ready(function(){
      $('.modal').modal();
      $('.button-collapse').sideNav();
    });
  </script>
</body>
</html>
<?php
  $ssh->exec('exit');
?>
