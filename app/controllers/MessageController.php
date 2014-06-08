<?php

class MessageController extends BaseController {
	
	public static function sendMessage($id, $from, $subject, $message) {
		$msg = new Message();
		$msg->user_id = $id;
		$msg->from_id = $from;
		$msg->subject = $subject;
		$msg->content = $message;
		$msg->save();
	}
	
	public function markMessageAsRead($id) {
		$msg = Message::find($id);
		$msg->viewed_at = date('Y-m-d H:i:s');
		$msg->save();
		return Response::json("Message marked as read.");
	}

	public static function sendMessageToAllUsers($from, $subject, $message) {
		foreach(HomeController::getAllActiveUsers() as $u) {
			MessageController::sendMessage($u->id, $from, $subject, $message);
		}
	}
}
?>