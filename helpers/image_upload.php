<?php 
$est_photo_tranche=(isset($_GET ['photo_tranche']) && $_GET ['photo_tranche'] == '1') 
				|| (isset($_POST['photo_tranche']) && $_POST['photo_tranche'] == '1');
?>
<html>
	<head>
		<script type="text/javascript">
			window.onload=function() {
                if (document.getElementById('pays')) {
                    document.getElementById('pays').value=window.parent.pays;
                    document.getElementById('magazine').value=window.parent.magazine;
                    document.getElementById('numero').value=window.parent.numero;
                }
			}
		</script>
	</head>
	<body>
<form method="POST" action="upload.php" enctype="multipart/form-data">
	 <input type="hidden" name="MAX_FILE_SIZE" value="<?=$est_photo_tranche ? 4000000 : 400000?>" />
	 <?php if (!isset($_GET['multiple'])) {?>
		 <input type="hidden" id="pays" name="pays" value="" />
		 <input type="hidden" id="magazine" name="magazine" value="" />
		 <input type="hidden" id="numero" name="numero" value="" />
	 <?php } ?>
	 <input type="hidden" id="photo_tranche" name="photo_tranche" value="<?=$est_photo_tranche ? 1 : 0?>" />
	 <input type="file" name="image" style="width: 260px"/><br />
	 <input type="submit" value="Envoyer" />
</form>
	</body>
	</html>