
<?php 
App::import('Controller', 'TicketController'); 
$TicketSupport = new TicketController();
?>
<section class="content">
    <div class="col-md-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= $title_for_layout; ?></h3>
            </div>
            <div class="box-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= $Lang->get('SUPPORT__SUBJECT') ?></th>
                            <th><?= $Lang->get('SUPPORT__AUTHOR') ?></th>
                            <th><?= $Lang->get('SUPPORT__STATE_TITLE') ?></th>
                            <th><?= $Lang->get('SUPPORT__CREATED') ?></th>
                            <th><?= $Lang->get('SUPPORT__ACTIONS') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tickets as $ticket): ?>
                        <tr>
                            <td><?= $ticket['Ticket']['subject']; ?></td>
                            <td><?= $TicketSupport->getUser('pseudo', $ticket['Ticket']['author']); ?></td>
                            <td>
                                <?php
                                switch($ticket['Ticket']['state'])
                                {
                                    case '0':
                                        echo '<div class="label label-warning">'.$Lang->get('SUPPORT__STATE_1').'</div>';
                                        break;
                                    case '1':
                                        echo '<div class="label label-info">'.$Lang->get('SUPPORT__STATE_0').'</div>';
                                        break;
                                    case '2':
                                        echo '<div class="label label-success">'.$Lang->get('SUPPORT__STATE_2').'</div>';
                                        break;
                                }
                                ?>
                            </td>
                            <td><?= date('d m Y', $ticket['Ticket']['created']); ?></td>
                            <td>
                                <?php if($ticket['Ticket']['state'] == 0 || $ticket['Ticket']['state'] == 1){ ?>
                                <form method="post" class="form-horizontal" data-ajax="true" data-redirect-url="?" action="<?= $this->Html->url(array('controller' => 'Ticket', 'action' => 'ajax_closa')) ?>">
                                    <a class="btn btn-primary" href="<?= $this->Html->url(array('plugin' => null, 'admin' => true, 'controller' => 'support', 'action' => 'ticket', $ticket['Ticket']['id'])); ?>">Voir</a>
                                    <input type="hidden" name="idTicket" value="<?= $ticket['Ticket']['id']; ?>">
                                    <button type="submit" class="btn btn-success" href="#"><?= $Lang->get('SUPPORT__CLOSE') ?></button>
                                </form>
                                <?php }else{ ?>
                                    <a class="btn btn-primary" href="<?= $this->Html->url(array('plugin' => null, 'admin' => true, 'controller' => 'support', 'action' => 'ticket', $ticket['Ticket']['id'])); ?>">Voir</a>
                                <?php }?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</section>