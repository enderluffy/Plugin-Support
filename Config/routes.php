<?php
Router::connect('/admin/support', array('controller' => 'Ticket', 'action' => 'index', 'plugin' => 'Support', 'admin' => true));
Router::connect('/support/ajax_clos', array('controller' => 'Ticket', 'action' => 'ajax_clos', 'plugin' => 'Support', 'admin' => false));
Router::connect('/admin/support/ajax_closa', array('controller' => 'Ticket', 'action' => 'ajax_closa', 'plugin' => 'Support', 'admin' => true));
Router::connect('/admin/support/ticket/*', array('controller' => 'Ticket', 'action' => 'ticket', 'plugin' => 'Support', 'admin' => true));
Router::connect('/admin/support/ajax_replya', array('controller' => 'Ticket', 'action' => 'ajax_replya', 'plugin' => 'Support', 'admin' => true));
Router::connect('/support', array('controller' => 'Ticket', 'action' => 'index', 'plugin' => 'Support'));
Router::connect('/support/ajax_create', array('controller' => 'Ticket', 'action' => 'ajax_create', 'plugin' => 'Support', 'admin' => false));
Router::connect('/support/ticket/ajax_reply', array('controller' => 'Ticket', 'action' => 'ajax_reply', 'plugin' => 'Support', 'admin' => false));
Router::connect('/support/create', array('controller' => 'Ticket', 'action' => 'create', 'plugin' => 'Support'));
Router::connect('/support/ticket/*', array('controller' => 'Ticket', 'action' => 'ticket', 'plugin' => 'Support'));

