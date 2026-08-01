// ── Core entities ────────────────────────────────────────────────────────────

export interface User {
    id: string;
    name: string;
    email: string;
    role: 'admin' | 'candidate' | 'embassy';
    email_verified_at: string | null;
    two_factor_secret: string | null;
    created_at: string;
}

export interface CandidateProfile {
    id: string;
    user_id: string;
    bio: string | null;
    phone: string | null;
    nationality: string | null;
    date_of_birth: string | null;
    photo_url: string | null;
}

export interface Embassy {
    id: string;
    official_name: string;
    country: string;
    city: string | null;
    website: string | null;
    contact_email: string | null;
}

export interface Offer {
    id: string;
    title: string;
    slug: string;
    country: string;
    city: string | null;
    offer_type: string;
    level: string | null;
    deadline: string | null;
    status: string;
    description: string | null;
    embassy: Embassy;
    created_at: string;
    updated_at: string;
}

// ── Public offers (paginated) ─────────────────────────────────────────────────

export interface PublicOffer {
    id: string;
    title: string;
    slug: string;
    country: string;
    city: string | null;
    offer_type: string;
    level: string | null;
    deadline: string | null;
    embassy_name: string;
}

export interface PaginatedOffers {
    data: PublicOffer[];
    current_page: number;
    last_page: number;
    total: number;
}

// ── Generic pagination ────────────────────────────────────────────────────────

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}

// ── Applications ──────────────────────────────────────────────────────────────

export type ApplicationStatus =
    | 'pending'
    | 'under_review'
    | 'accepted'
    | 'rejected'
    | 'withdrawn';

export interface StatusHistory {
    id: string;
    status: string;
    comment: string | null;
    created_at: string;
}

export interface Evaluation {
    id: string;
    score: number | null;
    comment: string | null;
    evaluator: User;
}

export interface Application {
    id: string;
    status: ApplicationStatus;
    created_at: string;
    updated_at: string;
    offer: Offer;
    candidate?: User & { candidate_profile?: CandidateProfile };
    status_histories: StatusHistory[];
    documents: Document[];
    conversation?: Conversation;
    evaluations?: Evaluation[];
}

// ── Documents ─────────────────────────────────────────────────────────────────

export interface Document {
    id: string;
    name: string;
    type: string;
    size: number;
    created_at: string;
}

// ── Messaging ─────────────────────────────────────────────────────────────────

export interface Message {
    id: string;
    content: string;
    sender: User;
    created_at: string;
}

export interface Conversation {
    id: string;
    messages: Message[];
    unread_count?: number;
    last_message?: Message;
    updated_at: string;
}

// ── Notifications ─────────────────────────────────────────────────────────────

export interface Notification {
    id: string;
    type: string;
    data: Record<string, unknown>;
    read_at: string | null;
    created_at: string;
}

// ── Sessions ──────────────────────────────────────────────────────────────────

export interface Session {
    id: string;
    name: string | null;
    device: string;
    browser: string;
    ip_address: string;
    last_activity: string;
    is_current: boolean;
}

// ── Embassy members ───────────────────────────────────────────────────────────

export interface EmbassyMember {
    id: string;
    user: User;
    role: string;
    joined_at: string;
}

export interface EmbassyInvitation {
    id: string;
    email: string;
    role: string;
    token: string;
    created_at: string;
    expires_at: string;
}

// ── CMS ───────────────────────────────────────────────────────────────────────

export interface CmsPage {
    id: string;
    title: string;
    slug: string;
    content: string;
    published: boolean;
    updated_at: string;
}

export interface CmsPost {
    id: string;
    title: string;
    slug: string;
    excerpt: string | null;
    content: string;
    published_at: string | null;
    created_at: string;
}

export interface Announcement {
    id: string;
    title: string;
    body: string;
    type: string;
    published_at: string | null;
    created_at: string;
}

// ── Search ────────────────────────────────────────────────────────────────────

export interface SearchAlert {
    id: string;
    name: string;
    filters: Record<string, string>;
    frequency: string;
    created_at: string;
}

// ── Analytics ─────────────────────────────────────────────────────────────────

export interface AnalyticsSummary {
    total_offers: number;
    total_applications: number;
    total_candidates: number;
    total_embassies: number;
    applications_by_status: Record<string, number>;
    offers_by_type: Record<string, number>;
}

// ── Audit ─────────────────────────────────────────────────────────────────────

export interface AuditLog {
    id: string;
    user: User | null;
    action: string;
    model_type: string;
    model_id: string | null;
    changes: Record<string, unknown> | null;
    ip_address: string | null;
    created_at: string;
}
