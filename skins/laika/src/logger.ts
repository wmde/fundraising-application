import { Notifier } from '@airbrake/browser';

const LOGGER_ERRBIT: string = 'errbit';
const LOGGER_CONSOLE: string = 'console';

interface Logger {
	notify( error: object ): void
}

class ErrbitLogger implements Logger {
	notifier: Notifier;

	constructor( host: string, projectKey: string ) {
		this.notifier = new Notifier( {
			host: host,
			projectId: 1,
			projectKey: projectKey,
		} );
	}

	notify( error: object ) {
		this.notifier.notify( error );
	}
}

class SilentLogger implements Logger {
	notify( error: object ) {}
}

class ConsoleLogger implements Logger {
	notify( error: object ) {
		console.log( error );
	}
}

export default function createLogger(): Logger {
	switch ( process.env.VUE_APP_LOGGER ) {
		case LOGGER_CONSOLE:
			return new ConsoleLogger();
		case LOGGER_ERRBIT:
			return new ErrbitLogger(
				process.env.VUE_APP_ERRBIT_HOST || '',
				process.env.VUE_APP_ERRBIT_PROJECT_KEY || ''
			);
		default:
			return new SilentLogger();
	}
}
