<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class PsyGame implements MessageComponentInterface {
    protected $clients;
	protected $storage;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
		$this->storage = array();
		$this->storage['psy_accuracy'] = array(0, 0); //global psy accuracy for all sessions
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
		$this->storage[$conn->resourceId] = array ('history' => array());

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo sprintf('Connection %d sending message "%s"' . "\n", $from->resourceId, $msg);
		
		$answer = 'E000@';
		
		if(strlen($msg) > 4)
		{
			$req = substr($msg, 0, 4);
			if($req == 'R001')
			{
				$psychics = array(rand(0,10), rand(0,10));
				
				$this->storage[$from->resourceId]['history'][] = $psychics;
				$answer = 'A001_' . strval($psychics[0]) . '_' . strval($psychics[1]);
			}
			else if($req == 'R002')
			{
				$req_val = substr($msg, 4);
				
				if(!is_numeric($req_val))
					$answer = 'E200';
				else
				{
					if(count($this->storage[$from->resourceId]['history'][array_key_last($this->storage[$from->resourceId]['history'])]) == 2)
					{
						$this->storage[$from->resourceId]['history'][array_key_last($this->storage[$from->resourceId]['history'])][] = intval($req_val);
						$answer = 'A002 OK';
						$last_key = array_key_last($this->storage[$from->resourceId]['history']);
						
						if(($req_val == $this->storage[$from->resourceId]['history'][$last_key][0]) && ($this->storage['psy_accuracy'][0] < PHP_INT_MAX))
							$this->storage['psy_accuracy'][0]++;
						
						if(($req_val == $this->storage[$from->resourceId]['history'][$last_key][1]) && ($this->storage['psy_accuracy'][1] < PHP_INT_MAX))
							$this->storage['psy_accuracy'][1]++;
					}
					else
						$answer = 'A002 FAIL';
				}				
			}
			else if($req == 'R003')
			{
				$answer = 'A003_' . json_encode(array('session_history' => $this->storage[$from->resourceId]['history'], 'global_psy_accuracy' => $this->storage['psy_accuracy']));
			}
			else
			{
				$answer = 'E100@';
			}
		}
			
		$from->send($answer);
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}