export interface PrivateDonorName {
    salutation: string,
    firstName: string,
    lastName: string
}

export interface CompanyDonorName {
    company: string
}

export interface DonorAddress {
    streetAddress: string,
    postalCode: string,
    city: string,
}

export interface DonorEmail {
    email: string
}

export interface PrivateDonor {
    name: PrivateDonorName,
    address: DonorAddress,
    email: DonorEmail
}

export interface CompanyDonor {
    name: CompanyDonorName,
    address: DonorAddress,
    email: DonorEmail
}

export interface EmailDonor {
    email: DonorEmail
}

export interface AnonymousDonor {}


