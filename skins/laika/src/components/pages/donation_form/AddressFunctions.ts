export default function setAddressType( context: any, type: any ) {
	context.$emit( 'address-type', type );
}
