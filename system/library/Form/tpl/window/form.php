<?php

	global $form_layout;
	$form_layout['window']['openTable'] = 	'	<div class="window">' . "\n" .
						'           <div class="title">{$title}</div>' . "\n" .
						'           <div class="form_box">' . "\n";
	$form_layout['window']['closeTable'] =  '           </div>' . "\n" .
						'       </div>' . "\n";
	$form_layout['window']['layout'] = 	'           <div><span>{$title}</span> {$input}</div>' . "\n";
	$form_layout['window']['rows'] = 	'           <div><span>{$title}</span><br/>{$input}</div>' . "\n";
	$form_layout['window']['buttons'] = 	'           <div>{$button}</div>' . "\n";
	$form_layout['window']['html'] = 	'           <div><span>{$html}</span></div>' . "\n";

?>