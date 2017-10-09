<?php
class TicketController extends AppController {

  function index() {
    if($this->isConnected){
      $this->set('title_for_layout',"Support");
      $this->layout = $this->Configuration->getKey('layout');
      $this->loadModel('Support.Ticket');

      $tickets = $this->Ticket->find('all', array('conditions' =>  array('Ticket.author' => array($this->User->getKey('id')))));
      $this->set(compact('tickets'));
    }else{
      throw new ForbiddenException();
    }
  }

  public function getUser($tag, $id)
  {
      $this->loadModel('User');
      $user = $this->User->getFromUser($tag, $id);
      return $user;
  }

  function admin_index(){
    if($this->isConnected AND $this->User->isAdmin()){
        $this->set('title_for_layout', $this->Lang->get('SUPPORT__GESTION').' - '.$this->Lang->get('SUPPORT__SUPPORT'));
        $tickets = $this->Ticket->find('all', array('order' => array('Ticket.id DESC')));
        $this->set(compact('tickets'));
        $this->layout = 'admin';
    }
        else        
            
            throw new ForbiddenException();      
  }

  function admin_ticket($id){
    if($this->isConnected AND $this->User->isAdmin()){
        $this->loadModel('Support.Ticket');
        $this->loadModel('Support.ReplyTicket');
        $ticket = $this->Ticket->find('first', array('conditions' => array("Ticket.id"  => array($id))));
        if(!empty($ticket)){
          $answers = $this->ReplyTicket->find('all', array('conditions' => array("ReplyTicket.ticket_id"  => array($id))));
          $this->set(compact('ticket'));
          $this->set(compact('answers'));
          $this->set('title_for_layout', $this->Lang->get('SUPPORT__TICKETNUMBER').''.$id);
        }else{
          throw new ForbiddenException();
        }
        $this->layout = 'admin';
    }
        else        
            
            throw new ForbiddenException();      
  }

  function ticket($id)
  {
    if($this->isConnected) {
        $this->loadModel('Support.Ticket');
        $this->loadModel('Support.ReplyTicket');
        $ticket = $this->Ticket->find('first', array('conditions' => array("Ticket.id"  => array($id))));
        if(!empty($ticket)){
          if($ticket['Ticket']['author'] == $this->User->getKey('id')){
              $answers = $this->ReplyTicket->find('all', array('conditions' => array("ReplyTicket.ticket_id"  => array($id))));
              $this->set(compact('ticket'));
              $this->set(compact('answers'));
              $this->set('title_for_layout', $this->Lang->get('SUPPORT__TICKETNUMBER').''.$id);
          }else{
              throw new ForbiddenException();
          }
        }else{
          throw new ForbiddenException();
        }
    }else{
        throw new ForbiddenException();
    }
  }

  function create() {
    if($this->isConnected) {
      $this->set('title_for_layout', $this->Lang->get('SUPPORT__CREATETITLE'));
    }else{
      throw new ForbiddenException();
    }
  }

  function ajax_create() {
      $this->autoRender = false;
      $this->response->type('json');
      if($this->isConnected) {
        if($this->request->is('Post')) {
          if(!empty($this->request->data['subject']) && !empty($this->request->data['reponse_text'])) {
            $contentTicket = $this->request->data['reponse_text'];
            if(strlen($contentTicket) > 255){
              $timeActu = time();
              $this->loadModel('Support.Ticket');
              $this->loadModel('Notification');
              $this->Ticket->set(array(
                'author' => $this->User->getKey('id'),
                'subject' => $this->request->data['subject'],
                'created' => $timeActu,
                'reponse_text' => $contentTicket
              ));
              $this->Ticket->save();
              $this->Notification->setToAdmin($this->User->getKey('pseudo').' '.$this->Lang->get('SUPPORT__NOTIF_CREATE'));
              $this->response->body(json_encode(array('statut' => true, 'msg' => $this->Lang->get('SUPPORT__SUCCESS_CREATE'))));
            } else {
              $this->response->body(json_encode(array('statut' => false, 'msg' => $this->Lang->get('SUPPORT__ERROR_PROBLEM_SHORT'))));
            }
          } else {
            $this->response->body(json_encode(array('statut' => false, 'msg' => $this->Lang->get('ERROR__FILL_ALL_FIELDS'))));
          }
        } else {
          $this->response->body(json_encode(array('statut' => false, 'msg' => "Internal Error")));
        }
      }else{
        throw new ForbiddenException();
      }
  }

  function admin_ajax_replya() {
    $this->autoRender = false;
    $this->response->type('json');
    if($this->isConnected AND $this->User->isAdmin()) {
      if($this->request->is('Post')) {
        $ticket = $this->Ticket->find('first', array('conditions' => array("Ticket.id"  => array($this->request->data['idTicket']))));
        if(!empty($ticket)){
          if($ticket['Ticket']['id'] == $this->request->data['idTicket']){
            if(!empty($this->request->data['reponse_text'])) {
              $contentAnswer = $this->request->data['reponse_text'];
              if(strlen($contentAnswer) > 255){
                $timeActu = time();
                $this->loadModel('Support.Ticket');
                $this->loadModel('Support.ReplyTicket');
                $this->loadModel('Notification');
                $this->Ticket->read(null, $ticket['Ticket']['id']);
                $this->Ticket->set(array(
                    'state' => '1'
                ));
                $this->Ticket->save();
                $this->ReplyTicket->set(array(
                  'ticket_id' => $this->request->data['idTicket'],
                  'reply' => $contentAnswer,
                  'created' => $timeActu,
                  'author' => $this->User->getKey('id'),
                  'type' => 1
                ));
                $this->ReplyTicket->save();
                $this->Notification->setToUser($this->User->getKey('pseudo').' '.$this->Lang->get('SUPPORT__NOTIF_ANSWER').' '.$ticket['Ticket']['id'].' !', $ticket['Ticket']['author']);
                $this->response->body(json_encode(array('statut' => true, 'msg' => $this->Lang->get('SUPPORT__SUCCESS_SEND_ANSWER'))));
              } else {
                $this->response->body(json_encode(array('statut' => false, 'msg' => $this->Lang->get('SUPPORT__ERROR_RESOLVE_SHORT'))));
              }
            } else {
              $this->response->body(json_encode(array('statut' => false, 'msg' => $this->Lang->get('ERROR__FILL_ALL_FIELDS'))));
            }
          } else {
            throw new ForbiddenException();
          }
        } else {
          throw new ForbiddenException();
        }
      } else {
        $this->response->body(json_encode(array('statut' => false, 'msg' => "Internal Error")));
      }
    }else{
      throw new ForbiddenException();
    }
  }

  function ajax_reply() {
    $this->autoRender = false;
    $this->response->type('json');
    if($this->isConnected) {
      if($this->request->is('Post')) {
        $ticket = $this->Ticket->find('first', array('conditions' => array("Ticket.id"  => array($this->request->data['idTicket']))));
        if(!empty($ticket)){
          if($ticket['Ticket']['author'] == $this->User->getKey('id')){
            if(!empty($this->request->data['reponse_text'])) {
              $contentAnswer = $this->request->data['reponse_text'];
              if(strlen($contentAnswer) > 255){
                $timeActu = time();
                $this->loadModel('Support.Ticket');
                $this->loadModel('Support.ReplyTicket');
                $this->loadModel('Notification');
                $this->Ticket->read(null, $ticket['Ticket']['id']);
                $this->Ticket->set(array(
                    'state' => '0'
                ));
                $this->Ticket->save();
                $this->ReplyTicket->set(array(
                  'ticket_id' => $this->request->data['idTicket'],
                  'reply' => $contentAnswer,
                  'created' => $timeActu,
                  'author' => $this->User->getKey('id'),
                  'type' => 0
                ));
                $this->ReplyTicket->save();
                $this->Notification->setToAdmin($this->User->getKey('pseudo').' '.$this->Lang->get('SUPPORT__NOTIF_ANSWER').' '.$ticket['Ticket']['id'].' !');
                $this->response->body(json_encode(array('statut' => true, 'msg' => $this->Lang->get('SUPPORT__SUCCESS_SEND_ANSWER'))));
              } else {
                $this->response->body(json_encode(array('statut' => false, 'msg' => $this->Lang->get('SUPPORT__ERROR_RESOLVE_SHORT'))));
              }
            } else {
              $this->response->body(json_encode(array('statut' => false, 'msg' => $this->Lang->get('ERROR__FILL_ALL_FIELDS'))));
            }
          } else {
            throw new ForbiddenException();
          }
        } else {
          throw new ForbiddenException();
        }
      } else {
        $this->response->body(json_encode(array('statut' => false, 'msg' => "Internal Error")));
      }
    }else{
      throw new ForbiddenException();
    }
  }

  function ajax_clos() {
    $this->autoRender = false;
    $this->response->type('json');
    $this->loadModel('Support.Ticket');
    $this->loadModel('Notification');
    if($this->isConnected) {
      if($this->request->is('Post')) {
        $ticket = $this->Ticket->find('first', array('conditions' => array("Ticket.id"  => array($this->request->data['idTicket']))));
        if(!empty($ticket)){
          if($ticket['Ticket']['author'] == $this->User->getKey('id')){
            $this->Ticket->read(null, $ticket['Ticket']['id']);
            $this->Ticket->set(array(
                'state' => '2'
            ));
            $this->Ticket->save();
            $this->Notification->setToAdmin($this->User->getKey('pseudo').' '.$this->Lang->get('SUPPORT__NOTIF_CLOS').' '.$ticket['Ticket']['id'].' !');
            $this->response->body(json_encode(array('statut' => true, 'msg' => $this->Lang->get('SUPPORT__SUCCESS_CLOSE'))));
          }else{
            throw new ForbiddenException();
          } 
        }else{
          $this->response->body(json_encode(array('statut' => false, 'msg' => $this->Lang->get('SUPPORT__TICKET_NOT_EXIST'))));
        }
      }else{
        throw new ForbiddenException();
      }
    }else{
      throw new ForbiddenException();
    }
  }

  function admin_ajax_closa() {
    $this->autoRender = false;
    $this->response->type('json');
    $this->loadModel('Support.Ticket');
    $this->loadModel('Notification');
    if($this->isConnected AND $this->User->isAdmin()) {
      if($this->request->is('Post')) {
        $ticket = $this->Ticket->find('first', array('conditions' => array("Ticket.id"  => array($this->request->data['idTicket']))));
        if(!empty($ticket)){
          $this->Ticket->read(null, $ticket['Ticket']['id']);
          $this->Ticket->set(array(
              'state' => '2'
          ));
          $this->Ticket->save();
          $this->Notification->setToUser($this->User->getKey('pseudo').' '.$this->Lang->get('SUPPORT__NOTIF_CLOS').' '.$ticket['Ticket']['id'].' !', $ticket['Ticket']['author']);
          $this->Notification->setToAdmin($this->User->getKey('pseudo').' '.$this->Lang->get('SUPPORT__NOTIF_CLOS').' '.$ticket['Ticket']['id'].' !');
          $this->response->body(json_encode(array('statut' => true, 'msg' => $this->Lang->get('SUPPORT__SUCCESS_CLOSE'))));
        }else{
          $this->response->body(json_encode(array('statut' => false, 'msg' => $this->Lang->get('SUPPORT__TICKET_NOT_EXIST'))));
        }
      }else{
        throw new ForbiddenException();
      }
    }else{
      throw new ForbiddenException();
    }
  }
}
