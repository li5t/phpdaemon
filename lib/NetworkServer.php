<?php

/**
 * Network server pattern
 * @extends ConnectionPool
 * @package Core
 *
 * @author Zorin Vasily <kak.serpom.po.yaitsam@gmail.com>
 */
class NetworkServer extends ConnectionPool {

	public function __construct($params = array()) {
		parent::__construct();
	}
	
	/**
	 * Called when a request to HTTP-server looks like WebSocket handshake query.
	 * @return void
	 */

	public function inheritFromRequest($req, $appInstance) {
		
		$connId = $req->attrs->connId;
		unset(Daemon::$process->queue[$connId . '-' . $req->attrs->id]);
		$buf = $appInstance->buf[$connId];
		unset($appInstance->buf[$connId]);
		unset($appInstance->poolState[$connId]);		
		unset(Daemon::$process->readPoolState[$connId]);
		
		$conn = new WebSocketConnection($connId, null, $req->attrs->server['REMOTE_ADDR'], $this);
		$this->list[$connId] = $conn;
		$conn->buffer = $buf;
		$set = event_buffer_set_callback(
			$buf, 
			array($conn, 'onReadEvent'),
			array($conn, 'onWriteEvent'),
			array($conn, 'onFailureEvent')
		);
		$conn->addr = $req->attrs->server['REMOTE_ADDR'];
		$conn->server = $req->attrs->server;
		$conn->firstline = true;
		$conn->onInheritanceFromRequest($req);
	}
		
	/**
	 * Called when worker is going to update configuration.
	 * @return void
	 */
	public function onConfigUpdated() {}

}
