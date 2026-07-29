export interface LogItem {
    id: number;
    user_id: number;
    action: string;
    description: string | null;
    user_agent: string | null;
    ip_address: string | null;
    device_type: string | null;
    device_brand: string | null;
    device_model: string | null;
    os: string | null;
    os_version: string | null;
    browser: string | null;
    browser_version: string | null;
    created_at: string;
    updated_at: string;
    user: { id: number; name: string } | null;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PageLink[];
}

export interface PageLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface DeviceInfo {
    browser: string | null;
    browser_version: string | null;
    os: string | null;
    os_version: string | null;
    device_type: string | null;
    device_brand: string | null;
    device_model: string | null;
    ip_address: string | null;
}