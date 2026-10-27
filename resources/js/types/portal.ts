export type ProgramCard = {
    slug: string;
    title: string;
    summary: string;
    issuer: 'internal' | 'bnsp';
    level: string | null;
    duration: string | null;
    price: number;
    effective_price: number;
    promo_active: boolean;
    next_batch_date: string | null;
    url: string;
};

export type AssessmentResult = {
    id: string;
    version: number;
    recommended_category: { slug: string; title: string };
    scores: { slug: string; title: string; score: number }[];
    taken_at: string;
    customer_id: string | null;
};

export type CustomerRow = {
    id: string;
    name: string;
    email: string;
    phone: string;
    company_name: string | null;
    masked_npwp: string | null;
    email_verified: boolean;
    created_at: string | null;
};

/** Bentuk JSON LengthAwarePaginator Laravel. */
export type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
};
