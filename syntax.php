<?php
if(!defined('DOKU_INC')) die();

class syntax_plugin_kanban extends DokuWiki_Syntax_Plugin {
    public function getType() { return 'substition'; }
    public function getSort() { return 150; }
    public function connectTo($mode) { 
        $this->Lexer->addSpecialPattern('\{\{kanban>.*?\}\}', $mode, 'plugin_kanban'); 
    }

    public function handle($match, $state, $pos, Doku_Handler $handler) {
		return [substr($match, 9, -2)];

    }

    public function render($mode, Doku_Renderer $renderer, $data) {
        if ($mode != 'xhtml') return false;
        global $conf;
		//$mydata = str_split("||",$data);
		list($boardName) = $data;
		//echo $boardName;//debugging line
        //list($boardName) = $mydata[0];
		//list($boardList) = $mydata[1];
		list($var1, $var2) = explode("+", $boardName); //added - 5-8-2026
		//Change the variable back to boardName
		$boardName = $var1;//added - 5-8-2026
        // Path to individual card files: data/kanban/[boardName]/*.json
        // $kanbanDir = $conf['savedir'] . '/kanban/' . $boardName . '/'; //WORKS!!
		$kanbanDir = $conf['savedir'] . '/kanban/' . $boardName . '/'; //added - 5-8-2026
        
        // Dynamically create the directory if it doesn't exist
        if (!is_dir($kanbanDir)) io_makeFileDir($kanbanDir . 'placeholder.txt');

        $renderer->doc .= '<div class="kanban-board" data-board="' . hsc($boardName) . '">';
        // $columns = ['Projects', 'WIP', 'On Hold', 'Done']; // WORKS!!
		// echo $var2;
		$headers = explode(",",$var2); //added - 5-8-2026
		$outpea = [];//added - 5-8-2026
		$thankee = count($headers);//added - 5-8-2026
		for($x=0;$x<$thankee;$x++){//added - 5-8-2026
			if($headers[$x] && $headers[$x] != "" && !is_null($headers[$x])){//added - 5-8-2026
					$outpea[$x] = $headers[$x];//added - 5-8-2026
			}else{//added - 5-8-2026
				continue;//added - 5-8-2026
			}//added - 5-8-2026
		}//added - 5-8-2026
		$columns = $outpea;//added - 5-8-2026
        $colVal=0;//added - 5-8-2026
		
        // Load all .json files in the board directory
        $savedCards = [];
        foreach (glob($kanbanDir . "*.json") as $file) {
            $card = json_decode(io_readFile($file), true);
            if ($card) $savedCards[] = $card;
        }
		
        foreach ($columns as $col) {
			
            $id = strtolower(str_replace(' ', '-', $col));
            $renderer->doc .= '<div class="kanban-col" data-id="' . $id . '">';
            $renderer->doc .= '  <h3>' . hsc($col) . '</h3>';
            $renderer->doc .= '  <div class="cards-container"><div class="kanban-card-locked" data-id=0"><div class="triple-lines"></div></div>';
            
            foreach ($savedCards as $card) {
                if ($card['column'] === $id) {
                    $renderer->doc .= $this->_renderCardHtml($card);
                }
            }
			if($colVal!==0){//added - 5-8-2026
				$renderer->doc .= '  </div></div>';//added - 5-8-2026
			}else{//added - 5-8-2026
				$renderer->doc .= '  </div><button class="add-card-btn">+ Add Card</button></div>';
				//$renderer->doc .= '  </div><button class="add-col-btn">+ Add Column</button> <button class="add-card-btn">+ Add Card</button></div>';//added - 5-8-2026
			}//added - 5-8-2026
			$colVal = ($colVal + 1);//added - 5-8-2026
        }
        $renderer->doc .= '</div>';
		
        return true;
    }

    private function _renderCardHtml($card) {
		$currNote = hsc($card['note']);
        $imp = hsc($card['importance'] ?? 'medium');
		if(hsc($card['checked']) !== "true"){
			$dat = "";
		}else{
			$dat = "checked";
		}
		$fullNotes = '';
		$notes=hsc($card['note']);
		$notif = explode("+",$notes);
		array_map('trim', $notif);
		foreach($notif as $note){
			if($note == ""){
				continue;
			}else{
			$fullNotes = $fullNotes . "+ " . $note . "<br>";
			}
		}
	
		//return the card data only if the card has not been checked complete
		if($dat != "checked"){
        return '<div class="kanban-card '.$imp.'" data-id="'.hsc($card['id']).'">'
		     . '<input type="checkbox" ' . $dat . '>'
             . '<strong class="card-title">'.hsc($card['name']).'</strong><div id="noteDiv" class="noteDiv">Content</div>'
             . '<div class="card-desc">'.hsc($card['desc'] ?? '').'</div>'
             . '<input class="btn-notes" value="+Add Note">'
			 . '<div class="card-note">' . $fullNotes . '</div></div>';
		}
    }
}
