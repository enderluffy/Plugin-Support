<?php
App::import('Controller', 'SupportController');
$Support = new SupportController();
?>
<section class="content">
	<h3 style="margin-top: 6px;"><?= $Lang->get('SUPPORT__SETTINGS_TITLE'); ?></h3>
	<div class="box">
		<div class="box-body">
			Bientôt
		</div>
	</div>
</section>
<div class="modal fade" id="modal-settings-suffix" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Afficher/Modifier le suffix des réponses</h4>
      </div>
      <div class="modal-body">
		<h4>Aperçu</h4>
		<hr>
		<blockquote>
            <?= $settings['SettingsSupport']['suffix_reply']; ?>
        </blockquote>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
        <button type="button" class="btn btn-primary">Modifier</button>
      </div>
    </div>
  </div>
</div>
<script>
	$('.modal-settings-suffix').click(function() {
		$('#modal-settings-suffix').modal();
	});
</script>