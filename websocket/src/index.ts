import { RestController } from './RestController';
import { RestifyServerFacade } from './Framework/Rest/RestifyServerFacade';
import { ConnectionRegistry } from './ConnectionRegistry';
import { SocketIOServerFacade } from './Framework/WebSocket/SocketIOServerFacade';
import { SocketController } from './SocketController';

const socketRegistry = new ConnectionRegistry();

const restServer = new RestifyServerFacade();
restServer.loadControllerDecorators(new RestController(socketRegistry));
restServer.listen(1338);

const socketServer = new SocketIOServerFacade();
socketServer.loadControllerDecorators(new SocketController(socketRegistry));
socketServer.listen(1337);

ch-ch-ch-changes!

Turn and face the strange
Ch-ch-changes!
don't want to be a richer man

Ch-ch-ch-ch-changes!
Turn and face the strange
Ch-ch-changes!

just gonna have to be a different man
Time may change me
But I can't trace time
