// from kebab-case to camelCase
export function camelizeName( fieldName: string ): string {
	return fieldName.replace( /-(\w)/g, ( _: string, firstChar: string ) => firstChar.toUpperCase() );
}
