<!DOCTYPE html>
<html>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<head>
	<title>Migrations</title>
</head>
<body>

<div class="container">
	<h1>Migrations</h1>
	<div class="alert alert-info">
		Existe uma diferença entre o <b>código</b> e o <b>banco de dados</b>.<br />
		Selecione abaixo o que você deseja <b>atualizadar no banco de dados</b>.<br />
		Caso não selecione o seu código será atualizado.<br />
		Se não souber o que significa essa tela simplesmente marque "<b>Não exibir mais essa tela</b>" e clique em salvar.
	</div>
	<form class="form" method="POST">
		<?php echo Form::hidden('token', $token); ?>
		<fieldset>
		<?php foreach ($queries as $table => $values) : ?>
			<div class="form-group">
				<label for="<?php echo $table; ?>"><?php echo str_replace('_', ' ', ORM::get_model_name($table)); ?></label>
				<?php foreach ($values as $value) : ?>
				<div class="checkbox">
					<label><input 
										type="checkbox" 
										name="queries[]" 
										<?php echo ((Arr::get($value, 'checked')) ? 'checked' : ''); ?> 
										value="<?php echo Arr::get($value, 'id'); ?>"> 
											<?php echo Arr::get($value, 'description'); ?>
					</label>
				</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
		</fieldset>
		<fieldset>
			<hr>
			<div class="checkbox">
		    <label>
		      <input type="checkbox" name="autogen_ignore"> Não exibir mais essa tela
		    </label>
		  </div>
			<button type="submit" class="btn btn-primary">Salvar</button>
		</fieldset>
	</form>
</div>

</body>
</html>