<?php $this->load->view('templates/header'); ?>

	<script type="text/javascript" src="<?php echo base_url('vendor/tinymce/tinymce/tinymce.min.js');?>"></script>

	<body>
		<div class="row">
			<div class="span4">
				<?php
					$href = str_replace("/system/Messages/write", "/system/Messages/send", $_SERVER["REQUEST_URI"]);
				?>
				<form id="sendForm" method="post" action="<?php echo $href; ?>">
					<div class="span4">
						To: <?php echo $receiver->vorname . " " . $receiver->nachname; ?><br/>
						Subject: <input type="text" value="" name="subject"><br/>
						<textarea id="bodyTextArea" name="body"></textarea>
					</div>
					<button type="submit">Send</button>
				</form> 
			</div>
		</div>

		<script>
			tinymce.init({
				selector: '#bodyTextArea'
			});
		</script>
	</body>
</html>
