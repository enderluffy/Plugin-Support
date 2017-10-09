<?php

class TicketController extends AppController
{

    public function index()
    {
        if (!$this->isConnected)
            throw new ForbiddenException();
        $this->set('title_for_layout', "Support");
        $this->loadModel('Support.Ticket');

        $tickets = $this->Ticket->find('all', array('conditions' => array('Ticket.author' => array($this->User->getKey('id')))));
        $this->set(compact('tickets'));
    }

    public function getUser($tag, $id)
    {
        $this->loadModel('User');
        return $this->User->getFromUser($tag, $id);
    }

    function admin_index()
    {
        if (!$this->Permissions->can('MANAGE_TICKETS'))
            throw new ForbiddenException();
        $this->set('title_for_layout', $this->Lang->get('SUPPORT__GESTION') . ' - ' . $this->Lang->get('SUPPORT__SUPPORT'));
        $tickets = $this->Ticket->find('all', array('order' => array('Ticket.id DESC')));
        $this->set(compact('tickets'));
        $this->layout = 'admin';
    }

    function admin_ticket($id)
    {
        if (!$this->Permissions->can('MANAGE_TICKETS'))
            throw new ForbiddenException();
        $this->loadModel('Support.Ticket');
        $this->loadModel('Support.ReplyTicket');
        $ticket = $this->Ticket->find('first', array('conditions' => array("Ticket.id" => array($id))));
        if (empty($ticket))
            throw new NotFoundException();
        $answers = $this->ReplyTicket->find('all', array('conditions' => array("ReplyTicket.ticket_id" => array($id))));

        $this->set(compact('ticket', 'answers'));
        $this->set('title_for_layout', $this->Lang->get('SUPPORT__TICKETNUMBER') . '' . $id);
        $this->layout = 'admin';
    }

    function ticket($id)
    {
        if (!$this->isConnected)
            throw new ForbiddenException();
        $this->loadModel('Support.Ticket');
        $this->loadModel('Support.ReplyTicket');
        $ticket = $this->Ticket->find('first', ['conditions' => ['id' => $id, 'author' => $this->User->getKey('id')]]);
        if (empty($ticket))
            throw new NotFoundException();

        $answers = $this->ReplyTicket->find('all', array('conditions' => array("ReplyTicket.ticket_id" => array($id))));
        $this->set(compact('ticket', 'answers'));
        $this->set('title_for_layout', $this->Lang->get('SUPPORT__TICKETNUMBER') . '' . $id);
    }

    function create()
    {
        if (!$this->isConnected)
            throw new ForbiddenException();
        $this->set('title_for_layout', $this->Lang->get('SUPPORT__CREATETITLE'));
    }

    function ajax_create()
    {
        if (!$this->isConnected)
            throw new ForbiddenException();
        if (!$this->request->is('post'))
            throw new BadRequestException();

        if (empty($this->request->data['subject']) || empty($this->request->data['reponse_text']))
            return $this->sendJSON(['statut' => false, 'msg' => $this->Lang->get('ERROR__FILL_ALL_FIELDS')]);
        $contentTicket = $this->request->data['reponse_text'];
        if (strlen($contentTicket) < 255)
            return $this->sendJSON(['statut' => false, 'msg' => $this->Lang->get('SUPPORT__ERROR_PROBLEM_SHORT')]);

        $this->loadModel('Support.Ticket');
        $this->loadModel('Notification');
        $this->Ticket->set(array(
            'author' => $this->User->getKey('id'),
            'subject' => $this->request->data['subject'],
            'reponse_text' => $contentTicket
        ));
        $this->Ticket->save();

        $this->Notification->setToAdmin($this->User->getKey('pseudo') . ' ' . $this->Lang->get('SUPPORT__NOTIF_CREATE'));
        $this->sendJSON(['statut' => true, 'msg' => $this->Lang->get('SUPPORT__SUCCESS_CREATE')]);
    }

    function admin_ajax_replya()
    {
        if (!$this->Permissions->can('MANAGE_TICKETS'))
            throw new ForbiddenException();
        if (!$this->request->is('post'))
            throw new BadRequestException();
        $ticket = $this->Ticket->find('first', ['conditions' => ['id' => $this->request->data['idTicket']]]);
        if (empty($ticket))
            throw new NotFoundException();

        $contentAnswer = $this->request->data['reponse_text'];
        if (strlen($contentAnswer) < 255)
            $this->sendJSON(['statut' => false, 'msg' => $this->Lang->get('SUPPORT__ERROR_RESOLVE_SHORT')]);

        $this->loadModel('Support.Ticket');
        $this->loadModel('Support.ReplyTicket');
        $this->loadModel('Notification');

        $this->Ticket->read(null, $ticket['Ticket']['id']);
        $this->Ticket->set(['state' => '1']);
        $this->Ticket->save();

        $this->ReplyTicket->set([
            'ticket_id' => $this->request->data['idTicket'],
            'reply' => $contentAnswer,
            'author' => $this->User->getKey('id'),
            'type' => 1
        ]);
        $this->ReplyTicket->save();

        $this->Notification->setToUser($this->User->getKey('pseudo') . ' ' . $this->Lang->get('SUPPORT__NOTIF_ANSWER') . ' ' . $ticket['Ticket']['id'] . ' !', $ticket['Ticket']['author']);
        $this->sendJSON(['statut' => true, 'msg' => $this->Lang->get('SUPPORT__SUCCESS_SEND_ANSWER')]);
    }

    function ajax_reply()
    {
        if (!$this->isConnected)
            throw new ForbiddenException();
        if (!$this->request->is('post'))
            throw new BadRequestException();

        $ticket = $this->Ticket->find('first', ['conditions' => ['id' => $this->request->data['idTicket'], 'author' => $this->User->getKey('id')]]);
        if (empty($ticket))
            throw new NotFoundException();

        $contentAnswer = $this->request->data['reponse_text'];
        if (strlen($contentAnswer) < 255)
            $this->sendJSON(['statut' => false, 'msg' => $this->Lang->get('SUPPORT__ERROR_RESOLVE_SHORT')]);

        $this->loadModel('Support.Ticket');
        $this->loadModel('Support.ReplyTicket');
        $this->loadModel('Notification');

        $this->Ticket->read(null, $ticket['Ticket']['id']);
        $this->Ticket->set(['state' => 0]);
        $this->Ticket->save();

        $this->ReplyTicket->set([
            'ticket_id' => $this->request->data['idTicket'],
            'reply' => $contentAnswer,
            'author' => $this->User->getKey('id'),
            'type' => 0
        ]);
        $this->ReplyTicket->save();

        $this->Notification->setToAdmin($this->User->getKey('pseudo') . ' ' . $this->Lang->get('SUPPORT__NOTIF_ANSWER') . ' ' . $ticket['Ticket']['id'] . ' !');
        $this->sendJSON(['statut' => true, 'msg' => $this->Lang->get('SUPPORT__SUCCESS_SEND_ANSWER')]);
    }

    function ajax_clos()
    {
        if (!$this->isConnected)
            throw new ForbiddenException();
        if (!$this->request->is('post'))
            throw new BadRequestException();

        $this->loadModel('Support.Ticket');
        $this->loadModel('Notification');
        $ticket = $this->Ticket->find('first', ['conditions' => ['id' => $this->request->data['idTicket'], 'author' => $this->User->getKey('id')]]);
        if (empty($ticket))
            throw new NotFoundException();

        $this->Ticket->read(null, $ticket['Ticket']['id']);
        $this->Ticket->set(['state' => 2]);
        $this->Ticket->save();

        $this->Notification->setToAdmin($this->User->getKey('pseudo') . ' ' . $this->Lang->get('SUPPORT__NOTIF_CLOS') . ' ' . $ticket['Ticket']['id'] . ' !');
        $this->sendJSON(['statut' => true, 'msg' => $this->Lang->get('SUPPORT__SUCCESS_CLOSE')]);
    }

    function admin_ajax_closa()
    {
        if (!$this->Permissions->can('MANAGE_TICKETS'))
            throw new ForbiddenException();
        if (!$this->request->is('post'))
            throw new BadRequestException();

        $this->loadModel('Support.Ticket');
        $this->loadModel('Notification');
        $ticket = $this->Ticket->find('first', ['conditions' => ['id' => $this->request->data['idTicket']]]);
        if (empty($ticket))
            throw new NotFoundException();

        $this->Ticket->read(null, $ticket['Ticket']['id']);
        $this->Ticket->set(['state' => 2]);
        $this->Ticket->save();

        $this->Notification->setToUser($this->User->getKey('pseudo') . ' ' . $this->Lang->get('SUPPORT__NOTIF_CLOS') . ' ' . $ticket['Ticket']['id'] . ' !', $ticket['Ticket']['author']);
        $this->Notification->setToAdmin($this->User->getKey('pseudo') . ' ' . $this->Lang->get('SUPPORT__NOTIF_CLOS') . ' ' . $ticket['Ticket']['id'] . ' !');
        $this->sendJSON(['statut' => true, 'msg' => $this->Lang->get('SUPPORT__SUCCESS_CLOSE')]);
    }
}
