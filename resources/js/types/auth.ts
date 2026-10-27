/** Admin yang login (hanya di area /admin). */
export type User = {
    id: number;
    name: string;
    email: string;
    role: 'owner' | 'operator';
    two_factor_enabled: boolean;
    avatar?: string;
};

/** Customer yang login (hanya di area portal); sengaja tanpa data sensitif. */
export type Customer = {
    name: string;
    email: string;
    email_verified: boolean;
};

export type Auth = {
    user?: User | null;
    customer?: Customer | null;
};

export type Site = {
    name: string;
    tagline: string | null;
    logo_url: string | null;
    contact: {
        email: string;
        phone: string;
        whatsapp: string;
        address: string;
    } | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
