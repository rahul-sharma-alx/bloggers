export type Post = {
    id: number;
    team_id: number;
    user_id: number;
    title: string;
    slug: string;
    body: string;
    featured_image: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    user?: { id: number; name: string };
    team?: { id: number; name: string; slug: string };
    comments?: Comment[];
    likes?: { id: number; user_id: number }[];
};

export type Comment = {
    id: number;
    post_id: number;
    user_id: number;
    body: string;
    created_at: string;
    user: { id: number; name: string };
};
