<?php echo Kohana::FILE_SECURITY.PHP_EOL.PHP_EOL; ?>
class <?php echo $class_name ?> extends <?php echo $class_extends ?> {
<?php if ($columns) : ?>

  protected $_table_columns = array(
<?php foreach ($columns as $column => $values) : ?>
    '<?php echo $column; ?>' => <?php echo str_replace(array("\n  ", "\n)", ' false', ' true'), array("\n\t\t\t", "\n\t\t)", ' FALSE', ' TRUE'), var_export($values, TRUE)); ?>,
<?php endforeach; ?>
  );
<?php endif; ?>
<?php if ( ! empty($has_many)) : ?>

  protected $_has_many = array(
<?php foreach ($has_many as $name => $values) : ?>
    '<?php echo $name; ?>' => <?php echo $values; ?>

<?php endforeach; ?>
  );
<?php endif; ?><?php if ( ! empty($belongs_to)) : ?>

  protected $_belongs_to = array(
<?php foreach ($belongs_to as $name => $values) : ?>
    '<?php echo $name; ?>' => <?php echo $values; ?>

<?php endforeach; ?>
  );
<?php endif; ?>
<?php if ( ! empty($rules)) : ?>

  public function rules()
  {
    return array(
<?php foreach ($rules as $name => $rule) : ?>
      '<?php echo $name; ?>' => array(
<?php foreach ($rule as $item) : ?>
        <?php echo $item; ?>

<?php endforeach; ?>
      ),
<?php endforeach; ?>
    );
  }

<?php endif; ?>
<?php if ( ! empty($labels)) : ?>

  public function labels()
  {
    return array(
<?php foreach ($labels as $name => $title) : ?>
      '<?php echo $name; ?>' => __('<?php echo $title; ?>'),
<?php endforeach; ?>
    );
  }

<?php endif; ?>
}