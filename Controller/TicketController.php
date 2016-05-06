<?php
class TicketController extends AppController {

  public function index() {
    $this->set('title_for_layout',"Support");

    $this->layout = $this->Configuration->getKey('layout');

    $this->loadModel('Support.Ticket');
  	$tickets = $this->Ticket->find('all', array('order' => array('id' => 'desc')));

    $this->loadModel('Support.ReplyTicket');
    $reply_tickets = $this->ReplyTicket->find('all');

    $nbr_tickets = $this->Ticket->find('count');
  	$nbr_tickets_resolved = $this->Ticket->find('count', array('conditions' => array('state' => 1)));
  	$nbr_tickets_unresolved = $this->Ticket->find('count', array('conditions' => array('state' => 0)));

    $this->set(compact('tickets', 'reply_tickets', 'nbr_tickets', 'nbr_tickets_resolved', 'nbr_tickets_unresolved'));
  }

    public function ajax_delete() {
      $this->autoRender = false;
      if($this->request->is('ajax')) {

      	$this->loadModel('Support.Ticket');

        $pseudo = $this->Ticket->find('first', array('conditions' => array('id' => $this->request->data['id'])));
        $pseudo = $pseudo['Ticket']['author'];

        if($this->isConnected AND $this->User->isAdmin() OR $this->isConnected AND $this->User->getKey('pseudo') == $pseudo AND $this->Permissions->can('DELETE_HIS_TICKET') OR $this->Permissions->can('DELETE_ALL_TICKETS')) {
      		$this->loadModel('Support.Ticket');

    			$this->Ticket->delete($this->request->data['id']);

          $this->loadModel('Support.ReplyTicket');
          $this->ReplyTicket->deleteAll(array('ticket_id' => $this->request->data['id']));

      		echo 'true';

      	} else {
      		throw new ForbiddenException();
      	}
      } else {
        throw new NotFoundException();
      }
    }

    public function ajax_reply_delete() {
      if($this->request->is('ajax')) {
        $this->autoRender = false;

        if($this->isConnected AND $this->User->isAdmin()) {

          $this->loadModel('Support.ReplyTicket');
          $this->ReplyTicket->delete($this->request->data['id']);

          echo 'true';

        } else {
          throw new ForbiddenException();
        }
      } else {
        throw new NotFoundException();
      }
    }

    public function ajax_resolved() {
    	if($this->request->is('ajax')) {
        $this->autoRender = false;

    			$this->loadModel('Support.Ticket');
		    	$pseudo = $this->Ticket->find('first', array('conditions' => array('id' => $this->request->data['id'])));
		    	$pseudo = $pseudo['Ticket']['author'];

		    	if($this->isConnected AND $this->User->isAdmin() OR $this->isConnected AND $this->User->getKey('pseudo') == $pseudo AND $this->Permissions->can('RESOLVE_HIS_TICKET') OR $this->Permissions->can('RESOLVE_ALL_TICKETS')) {

					  $this->Ticket->read(null, $this->request->data['id']);
					  $this->Ticket->set(array('state' => 1));
					  $this->Ticket->save();

					  echo 'true';

	    	} else {
	    		throw new ForbiddenException();
	    	}

    	} else {
        throw new NotFoundException();
		  }
    }

    public function ajax_reply() {
      if($this->request->is('ajax')) {
        $this->autoRender = false;

        if(!empty($this->request->data['reply']) && !empty($this->request->data['id'])) {

          $this->loadModel('Support.Ticket');
          $pseudo = $this->Ticket->find('first', array('conditions' => array('id' => $this->request->data['id'])));
          $pseudo = $pseudo['Ticket']['author'];

          if($this->isConnected AND $this->User->isAdmin() OR $this->isConnected AND $this->User->getKey('pseudo') == $pseudo AND $this->Permissions->can('REPLY_TO_HIS_TICKETS') OR $this->Permissions->can('REPLY_TO_ALL_TICKETS')) {

            $this->loadModel('Support.ReplyTicket');
            $this->ReplyTicket->create();
            $this->ReplyTicket->set(array('ticket_id' => $this->request->data['id'], 'reply' => $this->request->data['reply'], 'author' => $this->User->getKey('pseudo')));
            $this->ReplyTicket->save();

            echo json_encode(array('statut' => true, 'msg' => ''));

          } else {
            throw new ForbiddenException();
          }
        } else {
          echo json_encode(array('statut' => false, 'msg' => $this->Lang->get('ERROR__FILL_ALL_FIELDS')));
        }
      } else {
        throw new NotFoundException();
      }
    }

    public function ajax_post() {
      if($this->request->is('ajax')) {
        $this->autoRender = false;

        if(!empty($this->request->data['title']) AND !empty($this->request->data['content'])) {

          if($this->isConnected AND $this->Permissions->can('POST_TICKET')) {

            $data = array();

            $data['author'] = $this->User->getKey('pseudo');
            $data['private'] = $this->request->data['private'];
            $data['title'] = before_display($this->request->data['title']);
            $data['content'] = before_display($this->request->data['content']);

            $this->loadModel('Support.Ticket');
            $this->Ticket->create();
            $this->Ticket->set($data);
            $this->Ticket->save();

            echo json_encode(array('statut' => true, 'msg' => '', 'id' => $this->Ticket->getLastInsertId()));

          } else {
            throw new ForbiddenException();
          }
        } else {
        	echo json_encode(array('statut' => false, 'msg' => $this->Lang->get('ERROR__FILL_ALL_FIELDS')));
        }
      } else {
        throw new NotFoundException();
      }
    }
}
