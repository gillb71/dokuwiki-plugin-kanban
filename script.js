jQuery(function() {
    var $board = jQuery('.kanban-board');
	var $mycard = jQuery('.kanban-card');
    if (!$board.length) return;
    var boardName = $board.data('board');
	
    // Helper to send data to PHP
    function saveCardData($card) {
        jQuery.post(DOKU_BASE + 'lib/exe/ajax.php', {
            call: 'kanban_save', // Match this with your action.php check
            board: boardName,
            card_id: $card.data('id'),
            column: $card.closest('.kanban-col').data('id'),
            name: $card.find('.card-title').text(),
            importance: $card.attr('class').split(' ').filter(c => ['high','medium','low'].includes(c))[0] || 'medium',
            desc: $card.find('.card-desc').text(),
			note: $card.find('.card-note').text(),
			checked: $card.find('.checkbox-inline').prop('checked')
        }).done(function(res){ console.log("Saved:", res); });
    }

	// Prevent movement of the card column placeholder
	/*
	jQuery(function() {
    jQuery(".cards-container").sortable({
        // Only allow children WITHOUT the 'fixed-top-item' class to be moved
        items: "> div:not(.fixed-top)",
        
        // Listen for when an item is being moved to a new position
        update: function(event, ui) {
            // ui.item.index() returns the current position in the DOM
            if (ui.item.index() === 0) {
                // Find the fixed element
                var $fixed = jQuery(this).find(".fixed-top");
                // Immediately force the dropped item to go AFTER the fixed div
                ui.item.insertAfter($fixed);
            }
        }
		});
	});
	*/

    // Initialize Drag and Drop ONCE

    jQuery(".cards-container").sortable({
        connectWith: ".cards-container",
        placeholder: "ui-sortable-placeholder",
        tolerance: "pointer",

		
        start: function(e, ui) {
		//start-added
		const card = document.querySelector('.kanban-card');
		
		distance: 15, // Dragging won't trigger until moved 15px
        // Add and start the draggable class only AFTER the 15px threshold is met
		ui.item.addClass("is-dragging");
		//distance: -15, // Dragging won't trigger until moved -15px
        // Add and start the draggable class only AFTER the -15px threshold is met
		//ui.item.addClass("is-left-dragging");
		//end-added
            ui.placeholder.height(ui.item.outerHeight());
        },
        stop: function(event, ui) {
			//Remove the draggable class when you are done
			ui.item.removeClass("is-dragging");
			//ui.item.removeClass("is-left-dragging");
            //Fires once when dropped
            //alert(ui.item.text()); //debugging function works
			//alert(ui.item.data('id')); //debugging function
			//saveCardData(ui.item);
			//Modified to capture the text value in JQuery format
			//saveCardData(ui.item);
			saveCardData(ui.item); //replaced to bring about real name for saving
			//const divId = ui.item.closest('.kanban-col').data('id');
			//ui.item.closest('.kanban-col').preventDefault(); // This is required to allow a drop
			//console.log("Dropped on ID:", divId);
        }
    }).disableSelection();

    // Fix: Event Delegation for "Add Card"
    $board.on('click', '.add-card-btn', function() {
        var name = prompt("Job Name:");
        if (!name) return;
        var imp = (prompt("Importance (high, medium, low):", "medium") || "medium").toLowerCase();
        var cardId = "c" + Date.now();
		var note = "There are no notes on this card yet.";
        
        var $newCard = jQuery(`
            <div class="kanban-card ${imp}" data-id="${cardId}">
				<input type="checkbox">			
                <strong class="card-title">${name}</strong>
                <div class="card-desc">Click to add description...</div>
				<div style="color:black;">Refresh for Notes</div>
				<div class="card-note"></div>
            </div>
        `);
        
        jQuery(this).closest('.kanban-col').find('.cards-container').append($newCard);
        saveCardData($newCard);
    });
	
	//make the kanban cards add note button clickable
	$mycard.on('click', '.btn-notes', function() {
		//var $note = jQuery(this);
		//var currentNote = $note.text();
		//alert('this is my click');
		var mynote = prompt("Add Note:","");
		//Get the current username
		//var userlogin = JSINFO['user']; // Login username
		// Standard DokuWiki configuration object
		//attempt to query the user from the interface first
		var userlogin = jQuery('meta[name="plugin_do_user"]').attr('content');
		//console.log(JSINFO); //debugging line
		if (!userlogin && typeof JSINFO !== 'undefined') {
			//pull the user from php if the interface query fails
			userlogin = JSINFO['plugin_do_user'];
		}
		//return currentNote + " | " + note;
		if(mynote !== null){
			//Sanitize the input
			function sanitizeInlineCommands(inputText) {
				// Regex to match "javascript:" and other common inline command patterns
					const commandPattern = /javascript:|on\w+=|style="[^"]*expression[^"]*"|\+/gi;

					// Replace found commands with an empty string
					return inputText.replace(commandPattern, '');
			}

			// Example usage
			//const userInput = 'Hello <a href="javascript:alert(1)">Click</a>! <img src=x onerror=alert(1)>';
			const cleanedNote = sanitizeInlineCommands(mynote);
			//end sanitize
			//Get the current date string
			const date = new Date().toLocaleDateString('en-US');
			//Get the current time string
			const mytime = new Date().toLocaleTimeString();
			//Get the closest kanban-card to the object clicked
			var $note = jQuery(this).closest('.kanban-card');
			// filter() checks if $note IS the class; find() checks if it's INSIDE $note
			var $target = $note.filter('.card-note').add($note.find('.card-note'));
			// 2. Attach new data to the inner div with <br> delimiter
			$target.append("<br>+ " + userlogin + " - " + date + ":" + mytime + " - " + cleanedNote);

			saveCardData($note.closest('.kanban-card'));
		}
	});

    // Fix: Event Delegation for Clickable Description
    $board.on('click', '.card-desc', function() {
        var $desc = jQuery(this);
        var currentText = $desc.text() === "Click to add description..." ? "" : $desc.text();
        var newDesc = prompt("Enter Description:", currentText);
        if (newDesc !== null) {
            $desc.text(newDesc || "Click to add description...");
            saveCardData($desc.closest('.kanban-card'));
        }
    });
    // Fix: Event Delegation for Clickable Checkbox
    $board.on('click', '.checkbox-inline', function() {
        var $checkit = jQuery(this);
        var currentVal = $checkit.prop('checked');
        if ($checkit){
            saveCardData($checkit.closest('.kanban-card'));
        }
    });	
	
	
});
