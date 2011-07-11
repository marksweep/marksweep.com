<h1>Add a form</h1>

<?=$this->form->create($mform); ?>
    <?=$this->form->field('name');?>
    <?=$this->form->field('description', array('type' => 'textarea'));?>
    <?=$this->form->field('category', array('type'=>'select', 'list'=>$categories));?>
    <?=$this->form->field('local_id');?>
    <?=$this->form->submit('Add mform'); ?>
<?=$this->form->end(); ?>
