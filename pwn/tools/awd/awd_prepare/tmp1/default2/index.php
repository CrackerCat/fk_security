<html>
<head>
  <title>ping.php</title>
  <link rel="stylesheet" href="../css/bootstrap.css">
  <link rel="stylesheet" href="../css/nav.css">
  <meta charset="UTF-8">
</head>

<body>

  <?php include 'function.php';?>

  <div class="container mt-5 min-height main-body">
   
<div class="row">
<div class="col-8 m-auto">

	<form method=POST enctype="multipart/form-data" action="">
<h3 class="mb-5">验证主机是否存活</h3>

<div class="input-group mb-3">
  <input type="text" name="cmd" value="127.0.0.1" class="form-control" aria-label="Sizing example input" aria-describedby="inputGroup-sizing-default">
  <div class="input-group-append">
                        <input type=submit class="btn  btn-primary" value="PING">
                        </div>
                        </div>
                    </form>


 
            	
                 <?php 
                 	 
                 	 $cmd = $_POST["cmd"];
                 	 
                 	 if (filter($cmd) || filterip($cmd) || empty($cmd))
                 	 {
                 	 	echo "<pre>";
                 	 	system("ping -c 1 $cmd");
                 	 	echo "</pre>"; 
                 	 }
                 	 else
                 	 {
                 	 	 echo "# 你输入的命令包含敏感字符！请检查命令是否填写正确！";
                 	 }
                  ?>




            </div>
           
</div>
</div>

</body>
</html>