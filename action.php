<?php
if(!defined('DOKU_INC')) die();

class action_plugin_kanban extends DokuWiki_Action_Plugin {
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handle_ajax');
    }
	

 public function handle_started(Doku_Event $event, $param) {
        global $JSINFO;
        global $INPUT;

        // Get the logged-in username from the REMOTE_USER environment variable
        $user = $INPUT->server->str('REMOTE_USER');
        echo json_encode(['user' => $user]);
        if($user) {
            $JSINFO['user'] = $user;
        }
    }

	
public function handle_ajax(Doku_Event $event, $param) {
    if ($event->data !== 'kanban_save') return; // Must match call in JS
    $event->preventDefault();
    $event->stopPropagation();

    global $INPUT, $conf;
    $board  = $INPUT->str('board');
    $cardId = $INPUT->str('card_id');
	//$checkedval = $INPUT->str('checked');
	
	//if($checkedval = "" || is_null($checkedval) || !$checkedval){
		//$checkedval = "";
	//}
	
    // Dynamically build the path in data/kanban/
    $kanbanDir = '../' . '.' .$conf['savedir'] . '/kanban/' . $board . '/'; // added '../' . '.' . in the beginning path otherwise it writes to ./exe/data directory....annoying
    if (!is_dir($kanbanDir)) io_makeFileDir($kanbanDir . 'placeholder.txt');

	echo $kanbanDir; //debugging line

    $file = $kanbanDir . $cardId . '.json';
	
    $data = file_exists($file) ? json_decode(io_readFile($file), true) : ['id' => $cardId];
	

    //$newNote = $currNote . ' | ' . $data['note'];
    // Update fields sent by AJAX
    if ($INPUT->has('column'))     $data['column'] = $INPUT->str('column');
    if ($INPUT->has('name'))       $data['name']   = $INPUT->str('name');
    if ($INPUT->has('importance')) $data['importance'] = $INPUT->str('importance');
    if ($INPUT->has('desc'))       $data['desc']   = $INPUT->str('desc');
	if ($INPUT->has('checked'))    $data['checked']   = $INPUT->str('checked');
	//if ($INPUT->has('checked'))    $data['checked']   = $checkedval;
	if ($INPUT->has('note'))       $data['note']   = $INPUT->str('note');

    io_saveFile($file, json_encode($data));
    echo json_encode(['status' => 'success']);
}

}