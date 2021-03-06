<?php
/**
 *  This file is part of Exponent
 *  Exponent is free software; you can redistribute
 *  it and/or modify it under the terms of the GNU
 *  General Public License as published by the Free
 *  Software Foundation; either version 2 of the
 *  License, or (at your option) any later version.
 *
 * The file that holds the expCommentController class.
 *
 * @link http://www.gnu.org/licenses/gpl.txt GPL http://www.gnu.org/licenses/gpl.txt
 * @package Exponent-CMS
 * @copyright 2004-2011 OIC Group, Inc.
 * @author Adam Kessler <adam@oicgroup.net>
 * @version 2.0.0
 */
/**
 * This is the class expCommentController
 *
 * @subpackage Core-Controllers
 * @package Framework
 */

class expCommentController extends expController {
    public $base_class = 'expComment';
    protected $add_permissions = array('approve'=>"Approve Comments");
   	protected $remove_permissions = array('create');

    function displayname() { return "Comments"; }
    function description() { return "Use this module to add comments to a page."; }
    
	function edit() {
	    if (empty($this->params['content_id'])) {
	        flash('message',gt('An error occurred: No content id set.'));
            expHistory::back();  
	    } 
        /* The global constants can be overriden by passing appropriate params */ 
        //sure wish I could do this once in the constructor. sadly $this->params[] isn't set yet
        $require_login = empty($this->params['require_login']) ? COMMENTS_REQUIRE_LOGIN : $this->params['require_login'];
        $require_approval = empty($this->params['require_approval']) ? COMMENTS_REQUIRE_APPROVAL : $this->params['require_approval'];
        $require_notification = empty($this->params['require_notification']) ? COMMENTS_REQUIRE_NOTIFICATION : $this->params['require_notification'];
        $notification_email = empty($this->params['notification_email']) ? COMMENTS_NOTIFICATION_EMAIL : $this->params['notification_email'];
        
        
	    $id = empty($this->params['id']) ? null : $this->params['id'];
	    $comment = new expComment($id);
		assign_to_template(array(
		    'content_id'=>$this->params['content_id'],
		    'comment'=>$comment
		));
	}	
	
	function manage() {
	    expHistory::set('manageable', $this->params);
        
        /* The global constants can be overridden by passing appropriate params */
        //sure wish I could do this once in the constructor. sadly $this->params[] isn't set yet
        $require_login = empty($this->params['require_login']) ? COMMENTS_REQUIRE_LOGIN : $this->params['require_login'];
        $require_approval = empty($this->params['require_approval']) ? COMMENTS_REQUIRE_APPROVAL : $this->params['require_approval'];
        $require_notification = empty($this->params['require_notification']) ? COMMENTS_REQUIRE_NOTIFICATION : $this->params['require_notification'];
        $notification_email = empty($this->params['notification_email']) ? COMMENTS_NOTIFICATION_EMAIL : $this->params['notification_email'];
	    
	    $sql  = 'SELECT c.* FROM '.DB_TABLE_PREFIX.'_expComments c ';
        $sql .= 'JOIN '.DB_TABLE_PREFIX.'_content_expComments cnt ON c.id=cnt.expcomments_id ';
        $sql .= 'WHERE cnt.content_id='.$this->params['content_id']." AND cnt.content_type='".$this->params['content_type']."' ";
        //$sql .= 'AND c.approved=0';

        $page = new expPaginator(array(
            'model'=>'expComment',
            'sql'=>$sql, 
            'limit'=>10,
            'order'=>'created_at',
            'dir'=>'DESC',
            'controller'=>$this->baseclassname,
            'action'=>$this->params['action'],
            'columns'=>array('Approved'=>'approved', 'Poster'=>'name', 'Comment'=>'body'),
        ));
        
        assign_to_template(array(
            'page'=>$page,
		    'content_id'=>$this->params['content_id'],
		    'content_type'=>$this->params['content_type'],
        ));
	}
	
	function getComments() {
		global $user, $db;

        /* The global constants can be overridden by passing appropriate params */
        //sure wish I could do this once in the constructor. sadly $this->params[] isn't set yet
        $require_login = empty($this->params['require_login']) ? COMMENTS_REQUIRE_LOGIN : $this->params['require_login'];
        $require_approval = empty($this->params['require_approval']) ? COMMENTS_REQUIRE_APPROVAL : $this->params['require_approval'];
        $require_notification = empty($this->params['require_notification']) ? COMMENTS_REQUIRE_NOTIFICATION : $this->params['require_notification'];
        $notification_email = empty($this->params['notification_email']) ? COMMENTS_NOTIFICATION_EMAIL : $this->params['notification_email'];
        
        
//        $sql  = 'SELECT c.*, ua.image, u.username FROM '.DB_TABLE_PREFIX.'_expComments c ';
//        $sql .= 'JOIN '.DB_TABLE_PREFIX.'_content_expComments cnt ON c.id=cnt.expcomments_id ';
//        $sql .= 'JOIN '.DB_TABLE_PREFIX.'_user_avatar ua ON c.poster=ua.user_id ';
//        $sql .= 'JOIN '.DB_TABLE_PREFIX.'_user u ON c.poster=u.id ';
//        $sql .= 'WHERE cnt.content_id='.$this->params['content_id']." AND cnt.content_type='".$this->params['content_type']."' ";

        $sql  = 'SELECT c.* FROM '.DB_TABLE_PREFIX.'_expComments c ';
        $sql .= 'JOIN '.DB_TABLE_PREFIX.'_content_expComments cnt ON c.id=cnt.expcomments_id ';
        $sql .= 'WHERE cnt.content_id='.$this->params['content_id']." AND cnt.content_type='".$this->params['content_type']."' ";
        if (!($user->is_admin || $user->is_acting_admin)) {
            $sql .= 'AND c.approved=1';
        }

        $comments = new expPaginator(array(
            //'model'=>'expComment',
            'sql'=>$sql, 
            'limit'=>999,
            'order'=>'created_at',
            'controller'=>$this->baseclassname,
            'action'=>$this->params['action'],
            'columns'=>array('Readable Column Name'=>'Column Name'),
        ));

        // add username and avatar
        foreach ($comments->records as $key=>$record) {
            $commentor = new user($record->poster);
            $comments->records[$key]->username = $commentor->username;
            $comments->records[$key]->avatar = $db->selectObject('user_avatar',"user_id='".$record->poster."'");
        }
        // eDebug($sql, true);
        
        // count the unapproved comments
        if ($require_approval == 1 && $user->isAdmin()) {
            $sql  = 'SELECT count(com.id) as c FROM '.DB_TABLE_PREFIX.'_expComments com ';
            $sql .= 'JOIN '.DB_TABLE_PREFIX.'_content_expComments cnt ON com.id=cnt.expcomments_id ';
            $sql .= 'WHERE cnt.content_id='.$this->params['content_id']." AND cnt.content_type='".$this->params['content_type']."' ";
            $sql .= 'AND com.approved=0';
            $unapproved = $db->countObjectsBySql($sql);
        } else {
            $unapproved = 0;
        }        
        
        $this->config = $this->params['config'];
        
        assign_to_template(array(
            'comments'=>$comments,
            'unapproved'=>$unapproved, 
			'content_id'=>$this->params['content_id'], 
			'content_type'=>$this->params['content_type'],
			'user'=>$user,
			'hideform'=>$this->params['hideform'],
			'hidecomments'=>$this->params['hidecomments'],
			'title'=>$this->params['title'],
			'formtitle'=>$this->params['formtitle'],
		));
	}

    function update() {
        global $db, $user;
        
        /* The global constants can be overridden by passing appropriate params */
        //sure wish I could do this once in the constructor. sadly $this->params[] isn't set yet
        $require_login = empty($this->params['require_login']) ? COMMENTS_REQUIRE_LOGIN : $this->params['require_login'];
        $require_approval = empty($this->params['require_approval']) ? COMMENTS_REQUIRE_APPROVAL : $this->params['require_approval'];
        $require_notification = empty($this->params['require_notification']) ? COMMENTS_REQUIRE_NOTIFICATION : $this->params['require_notification'];
        $notification_email = empty($this->params['notification_email']) ? COMMENTS_NOTIFICATION_EMAIL : $this->params['notification_email'];
        
        // check the anti-spam control
        if (!$user->isLoggedIn())
        {
            expValidator::check_antispam($this->params, gt("Your comment could not be posted because anti-spam verification failed.  Please try again."));
        }
        
        // figure out the name and email address
        if (!empty($user->id) && empty($this->params['id'])) {
            $this->params['name'] = $user->firstname." ".$user->lastname;
            $this->params['email'] = $user->email;
        }
                        
        // save the comment
        if (empty($require_approval)) {
            $this->expComment->approved=1;
        }
        $this->expComment->update($this->params);
        
        // attach the comment to the datatype it belongs to (blog, news, etc..);
		$obj->content_type = $this->params['content_type'];
		$obj->content_id = $this->params['content_id'];
		$obj->expcomments_id = $this->expComment->id;
		if(isset($this->params['subtype'])) $obj->subtype = $this->params['subtype'];
		$db->insertObject($obj, $this->expComment->attachable_table);
		
		$msg = 'Thank you for posting a comment.';
		if ($require_approval == 1 && !$user->isAdmin()) {
		    $msg .= ' '.gt('Your comment is now pending approval. You will receive an email to').' ';
		    $msg .= $this->expComment->email.' '.gt('letting you know when it has been approved.');
		}
		
		if ($require_notification && !$user->isAdmin()) {
		    $this->sendNotification($this->expComment,$this->params);
		}
        if ($require_approval==1 && $this->params['approved']==1) {
		    $this->sendApprovalNotification($this->expComment,$this->params);
        }
		//if ($require_notification && !$user->isAdmin()) {
		//}
		
		flash('message', $msg);
		
		expHistory::back();
	}
	
	public function approve() {
	    expHistory::set('editable', $this->params);
        
        /* The global constants can be overriden by passing appropriate params */ 
        //sure wish I could do this once in the constructor. sadly $this->params[] isn't set yet
        $require_login = empty($this->params['require_login']) ? COMMENTS_REQUIRE_LOGIN : $this->params['require_login'];
        $require_approval = empty($this->params['require_approval']) ? COMMENTS_REQUIRE_APPROVAL : $this->params['require_approval'];
        $require_notification = empty($this->params['require_notification']) ? COMMENTS_REQUIRE_NOTIFICATION : $this->params['require_notification'];
        $notification_email = empty($this->params['notification_email']) ? COMMENTS_NOTIFICATION_EMAIL : $this->params['notification_email'];
	    
	    if (empty($this->params['id'])) {
	        flash('error', gt('No ID supplied for comment to approve'));
	        expHistory::back();
	    }
	    
	    $comment = new expComment($this->params['id']);
	    assign_to_template(array('comment'=>$comment));
	}
	
	public function approve_submit() {
	    if (empty($this->params['id'])) {
	        flash('error', gt('No ID supplied for comment to approve'));
	        expHistory::back();
	    }
        
        /* The global constants can be overriden by passing appropriate params */ 
        //sure wish I could do this once in the constructor. sadly $this->params[] isn't set yet
        $require_login = empty($this->params['require_login']) ? COMMENTS_REQUIRE_LOGIN : $this->params['require_login'];
        $require_approval = empty($this->params['require_approval']) ? COMMENTS_REQUIRE_APPROVAL : $this->params['require_approval'];
        $require_notification = empty($this->params['require_notification']) ? COMMENTS_REQUIRE_NOTIFICATION : $this->params['require_notification'];
        $notification_email = empty($this->params['notification_email']) ? COMMENTS_NOTIFICATION_EMAIL : $this->params['notification_email'];
	    
	    $comment = new expComment($this->params['id']);
	    $comment->body = $this->params['body'];
	    $comment->approved = $this->params['approved'];
	    $comment->save();
	    expHistory::back();
	}
	
	public function approve_toggle() {
	    if (empty($this->params['id'])) return;
        
        /* The global constants can be overriden by passing appropriate params */ 
        //sure wish I could do this once in the constructor. sadly $this->params[] isn't set yet
        $require_login = empty($this->params['require_login']) ? COMMENTS_REQUIRE_LOGIN : $this->params['require_login'];
        $require_approval = empty($this->params['require_approval']) ? COMMENTS_REQUIRE_APPROVAL : $this->params['require_approval'];
        $require_notification = empty($this->params['require_notification']) ? COMMENTS_REQUIRE_NOTIFICATION : $this->params['require_notification'];
        $notification_email = empty($this->params['notification_email']) ? COMMENTS_NOTIFICATION_EMAIL : $this->params['notification_email'];
        
        
	    $comment = new expComment($this->params['id']);
	    $comment->approved = $comment->approved == 1 ? 0 : 1;
	    if ($comment->approved) {
		    $this->sendApprovalNotification($comment,$this->params);
	    }
	    $comment->save();
	    expHistory::back();
	}
	public function delete() {
	    global $db;
        
        /* The global constants can be overriden by passing appropriate params */ 
        //sure wish I could do this once in the constructor. sadly $this->params[] isn't set yet
        $require_login = empty($this->params['require_login']) ? COMMENTS_REQUIRE_LOGIN : $this->params['require_login'];
        $require_approval = empty($this->params['require_approval']) ? COMMENTS_REQUIRE_APPROVAL : $this->params['require_approval'];
        $require_notification = empty($this->params['require_notification']) ? COMMENTS_REQUIRE_NOTIFICATION : $this->params['require_notification'];
        $notification_email = empty($this->params['notification_email']) ? COMMENTS_NOTIFICATION_EMAIL : $this->params['notification_email'];
	    
	    if (empty($this->params['id'])) {
	        flash('error', gt('Missing id for the comment you would like to delete'));
	        expHistory::back();
	    }
	    
	    // delete the comment
        $comment = new expComment($this->params['id']);
        $rows = $comment->delete();
        
        // delete the assocication too
        $db->delete($comment->attachable_table, 'expcomments_id='.$this->params['id']);        
        
        // send the user back where they came from.
        expHistory::back();
	}
	
	private function sendNotification($comment,$params) {
	    global $db;
	    if (empty($comment)) return false;
        
        //eDebug($comment,1);
        /* The global constants can be overriden by passing appropriate params */ 
        //sure wish I could do this once in the constructor. sadly $this->params[] isn't set yet
        $require_login = empty($this->params['require_login']) ? COMMENTS_REQUIRE_LOGIN : $this->params['require_login'];
        $require_approval = empty($this->params['require_approval']) ? COMMENTS_REQUIRE_APPROVAL : $this->params['require_approval'];
        $require_notification = empty($this->params['require_notification']) ? COMMENTS_REQUIRE_NOTIFICATION : $this->params['require_notification'];
        $notification_email = empty($this->params['notification_email']) ? COMMENTS_NOTIFICATION_EMAIL : $this->params['notification_email'];
	    
	    // setup some email variables.
	    $subject = gt('Notification of a New Comment Posted to').' '.URL_BASE;
        $tos = explode(',', str_replace(' ', '', $notification_email));
        $tos = array_filter($tos);
        if (empty($tos)) return false;

        $model = new $params['content_type']($params['content_id']);
	    $loc = expUnserialize($model->location_data);

        $posting = makelink(array('controller'=>$params['content_type'], 'action'=>'show', 'id'=>$params['content_id'],"src"=>$loc->src));
        $editlink = makelink(array('controller'=>'expComment', 'action'=>'edit', 'id'=>$comment->id));
        
        // make the email body
        $body = '<h1>'.gt('New Comment Posted').'</h1>';
        $body .= '<h2>'.gt('Posted By').'</h2>';
        $body .= '<p>'.$comment->name."</p>";
        $body .= '<h2>'.gt('Poster\'s Email').'</h2>';
        $body .= '<p>'.$comment->email."</p>";
        $body .= '<h2>'.gt('Comment').'</h2>';
        $body .= '<p>'.$comment->body.'</p>';
        $body .= '<h3>'.gt('View posting').'</h3>';
        $body .= '<a href="'.$posting.'">'.$posting.'</a>';
        //1$body .= "<br><br>";
        $body .= '<h3>'.gt('Edit / Approve comment').'</h3>';
        $body .= '<a href="'.$editlink.'">'.$editlink.'</a>';
        
        // create the mail message
        $mail = new expMail();        
        $mail->quickSend(array(
                'html_message'=>$body,
			    'to'=>$tos,
//			    'from'=>trim(SMTP_FROMADDRESS),
//			    'from_name'=>trim(ORGANIZATION_NAME),
				'from'=>array(trim(SMTP_FROMADDRESS)=>trim(ORGANIZATION_NAME)),
			    'subject'=>$subject,
        ));
        
        return true;
	}

	private function sendApprovalNotification($comment,$params) {
	    if (empty($comment)) return false;
        
        /* The global constants can be overriden by passing appropriate params */ 
        //sure wish I could do this once in the constructor. sadly $this->params[] isn't set yet
        $require_login = empty($this->params['require_login']) ? COMMENTS_REQUIRE_LOGIN : $this->params['require_login'];
        $require_approval = empty($this->params['require_approval']) ? COMMENTS_REQUIRE_APPROVAL : $this->params['require_approval'];
        $require_notification = empty($this->params['require_notification']) ? COMMENTS_REQUIRE_NOTIFICATION : $this->params['require_notification'];
        $notification_email = empty($this->params['notification_email']) ? COMMENTS_NOTIFICATION_EMAIL : $this->params['notification_email'];
	    
	    // setup some email variables.
	    $subject = gt('Notification of Comment Approval on').' '.URL_BASE;
        $tos = explode(',', str_replace(' ', '', $notification_email));
        $tos[] = $comment->email;
		$tos = array_filter($tos);
		if (empty($tos)) return false;

        $model = new $params['content_type']($params['content_id']);
	    $loc = expUnserialize($model->location_data);

        $posting = makelink(array('controller'=>$params['content_type'], 'action'=>'show', 'id'=>$params['content_id'],"src"=>$loc->src));
        $editlink = makelink(array('controller'=>'expComment', 'action'=>'edit', 'id'=>$comment->id));
                
        // make the email body
        $body = '<h1>'.gt('Comment Approved').'</h1>';
        $body .= '<h2>'.gt('Posted By').'</h2>';
        $body .= '<p>'.$comment->name."</p>";
        $body .= '<h2>'.gt('Poster\'s Email').'</h2>';
        $body .= '<p>'.$comment->email.'</p>';
        $body .= '<h2>'.gt('Comment').'</h2>';
        $body .= '<p>'.$comment->body."</p>";
        $body .= '<h3>'.gt('View posting').'</h3>';
        $body .= '<a href="'.$posting.'">'.$posting.'</a>';

        // create the mail message
        $mail = new expMail();        
        $mail->quickSend(array(
                'html_message'=>$body,
			    'to'=>$tos,
//			    'from'=>trim(SMTP_FROMADDRESS),
//			    'from_name'=>trim(ORGANIZATION_NAME),
			    'from'=>array(trim(SMTP_FROMADDRESS)=>trim(ORGANIZATION_NAME)),
			    'subject'=>$subject,
        ));
        
        return true;
	}

}

?>