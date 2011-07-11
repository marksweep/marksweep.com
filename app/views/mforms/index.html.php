<h1>MForms</h1>

<?php
// Loop through the forms
foreach ($mforms as $mform) {
	// Layout the forms using elements
	echo $this->_render('element', 'mforms/mform', array('mform' => $mform));

}
?>