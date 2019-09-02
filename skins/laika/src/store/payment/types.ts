import { Payment } from '@/view_models/Payment';
import { Prefillable } from '@/view_models/Prefillable';

export type DonationPayment = Payment & Prefillable;
