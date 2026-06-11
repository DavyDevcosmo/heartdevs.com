-- Generated schema from migrations (applied in order)

CREATE TABLE IF NOT EXISTS messages (
    id UUID NOT NULL,
    provider_message_id VARCHAR(255) NOT NULL,
    channel_id VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    obtained_experience INT NOT NULL,
    sent_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    metadata JSONB NOT NULL,
    reactions_count INT NOT NULL,
    reactions_total INT NOT NULL,
    kind VARCHAR(255) NOT NULL,
    raw_message_type SMALLINT NOT NULL,
    source_kind VARCHAR(255) NOT NULL,
    is_pinned BOOLEAN NOT NULL,
    mentions_everyone BOOLEAN NOT NULL,
    mention_role_count SMALLINT NOT NULL,
    edited_at TIMESTAMP NOT NULL,
    reply_to_provider_message_id INDEX NOT NULL,
    messages_tenant_sent_at_idx DROPINDEX NOT NULL,
    messages_tenant_channel_sent_at_idx DROPINDEX NOT NULL,
    messages_tenant_kind_idx DROPINDEX NOT NULL,
    messages_reply_to_provider_message_id_idx DROPINDEX NOT NULL,
    messages_tenant_provider_message_id_unique DROPUNIQUE NOT NULL,
    external_identity_id UUID NOT NULL
);

CREATE INDEX idx_messages_77aa49934923369dd9700fd86743265d ON messages (external_identity_id);
ALTER TABLE messages ADD CONSTRAINT fk_messages_provider_id FOREIGN KEY (provider_id) REFERENCES providers(id);
ALTER TABLE messages ADD CONSTRAINT fk_messages_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);
ALTER TABLE messages ADD CONSTRAINT fk_messages_reply_to_message_id FOREIGN KEY (reply_to_message_id) REFERENCES messages(id);

CREATE TABLE IF NOT EXISTS voice_messages (
    id BIGSERIAL PRIMARY KEY,
    channel_name VARCHAR(255) NOT NULL,
    state VARCHAR(255) NOT NULL,
    obtained_experience INT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    provider_message_id VARCHAR(255) NOT NULL,
    occurred_at TIMESTAMP NOT NULL,
    external_identity_id UUID NOT NULL,
    PRIMARY KEY (id)
);

ALTER TABLE voice_messages ADD CONSTRAINT fk_voice_messages_provider_id FOREIGN KEY (provider_id) REFERENCES providers(id);
ALTER TABLE voice_messages ADD CONSTRAINT fk_voice_messages_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);

CREATE TABLE IF NOT EXISTS interactions (
    id UUID NOT NULL,
    character_id UUID NOT NULL,
    tenant_id UUID NOT NULL,
    type VARCHAR(255) NOT NULL,
    provider VARCHAR(255) NOT NULL,
    value_tier VARCHAR(255) NOT NULL,
    coins_min INT NOT NULL,
    coins_max INT NOT NULL,
    coins_awarded INT NOT NULL,
    xp_awarded INT NOT NULL,
    status VARCHAR(255) NOT NULL,
    source NULLABLEUUIDMORPHS NOT NULL,
    external_ref VARCHAR(255) NOT NULL,
    metadata JSONB NOT NULL,
    occurred_at TIMESTAMP NOT NULL,
    reviewed_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE interactions ADD CONSTRAINT fk_interactions_character_id FOREIGN KEY (character_id) REFERENCES characters(id);
ALTER TABLE interactions ADD CONSTRAINT fk_interactions_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);

CREATE TABLE IF NOT EXISTS moderation_events (
    id UUID NOT NULL,
    tenant_id UUID NOT NULL,
    external_identity_id UUID NOT NULL,
    moderator_identity_id UUID NOT NULL,
    type VARCHAR(255) NOT NULL,
    reason TEXT NOT NULL,
    source_identity_id UUID NOT NULL,
    source_message_id FOREIGN NOT NULL,
    metadata JSONB NOT NULL,
    occurred_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE moderation_events ADD CONSTRAINT fk_moderation_events_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);
ALTER TABLE moderation_events ADD CONSTRAINT fk_moderation_events_external_identity_id FOREIGN KEY (external_identity_id) REFERENCES external_identities(id);
ALTER TABLE moderation_events ADD CONSTRAINT fk_moderation_events_moderator_identity_id FOREIGN KEY (moderator_identity_id) REFERENCES external_identities(id);
ALTER TABLE moderation_events ADD CONSTRAINT fk_moderation_events_source_identity_id FOREIGN KEY (source_identity_id) REFERENCES external_identities(id);

CREATE TABLE IF NOT EXISTS activity_reactions (
    id UUID NOT NULL,
    tenant_id UUID NOT NULL,
    reactable UUIDMORPHS NOT NULL,
    emoji_key VARCHAR(128) NOT NULL,
    emoji_id VARCHAR(255) NOT NULL,
    emoji_name VARCHAR(255) NOT NULL,
    count INT NOT NULL,
    count_burst INT NOT NULL,
    count_normal INT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE activity_reactions ADD CONSTRAINT fk_activity_reactions_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);

CREATE TABLE IF NOT EXISTS message_mentions (
    id UUID NOT NULL,
    tenant_id UUID NOT NULL,
    message_id UUID NOT NULL,
    mentioned_identity_id UUID NOT NULL,
    mentioned_provider_account_id VARCHAR(255) NOT NULL,
    mentioned_username VARCHAR(255) NOT NULL,
    position SMALLINT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE message_mentions ADD CONSTRAINT fk_message_mentions_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);
ALTER TABLE message_mentions ADD CONSTRAINT fk_message_mentions_message_id FOREIGN KEY (message_id) REFERENCES messages(id);
ALTER TABLE message_mentions ADD CONSTRAINT fk_message_mentions_mentioned_identity_id FOREIGN KEY (mentioned_identity_id) REFERENCES external_identities(id);

CREATE TABLE IF NOT EXISTS message_threads (
    id UUID NOT NULL,
    tenant_id UUID NOT NULL,
    message_id INDEX NOT NULL,
    provider_thread_id VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    archived BOOLEAN NOT NULL,
    auto_archive_duration INT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE message_threads ADD CONSTRAINT fk_message_threads_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);
ALTER TABLE message_threads ADD CONSTRAINT fk_message_threads_message_id FOREIGN KEY (message_id) REFERENCES messages(id);

CREATE TABLE IF NOT EXISTS message_attachments (
    id UUID NOT NULL,
    tenant_id UUID NOT NULL,
    message_id INDEX NOT NULL,
    provider_attachment_id VARCHAR(255) NOT NULL,
    url TEXT NOT NULL,
    filename TEXT NOT NULL,
    content_type TEXT NOT NULL,
    size BIGINT NOT NULL,
    width INT NOT NULL,
    height INT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE message_attachments ADD CONSTRAINT fk_message_attachments_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);
ALTER TABLE message_attachments ADD CONSTRAINT fk_message_attachments_message_id FOREIGN KEY (message_id) REFERENCES messages(id);

CREATE TABLE IF NOT EXISTS message_embeds (
    id UUID NOT NULL,
    tenant_id UUID NOT NULL,
    message_id INDEX NOT NULL,
    url TEXT NOT NULL,
    title TEXT NOT NULL,
    description TEXT NOT NULL,
    source_domain VARCHAR(255) NOT NULL,
    kind VARCHAR(255) NOT NULL,
    thumbnail_url TEXT NOT NULL,
    raw JSONB NOT NULL,
    position SMALLINT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE message_embeds ADD CONSTRAINT fk_message_embeds_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);
ALTER TABLE message_embeds ADD CONSTRAINT fk_message_embeds_message_id FOREIGN KEY (message_id) REFERENCES messages(id);

CREATE TABLE IF NOT EXISTS membership_events (
    id UUID NOT NULL,
    tenant_id UUID NOT NULL,
    external_identity_id UUID NOT NULL,
    kind VARCHAR(255) NOT NULL,
    occurred_at TIMESTAMP NOT NULL,
    provider_message_id VARCHAR(255) NOT NULL,
    metadata JSONB NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE membership_events ADD CONSTRAINT fk_membership_events_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);
ALTER TABLE membership_events ADD CONSTRAINT fk_membership_events_external_identity_id FOREIGN KEY (external_identity_id) REFERENCES external_identities(id);

CREATE TABLE IF NOT EXISTS activity_timeline (
    id UUID NOT NULL,
    user_id UUID NOT NULL,
    tenant_id UUID NOT NULL,
    postable UUIDMORPHS NOT NULL,
    root_id UUID NOT NULL,
    parent_id UUID NOT NULL,
    is_ignored BOOLEAN NOT NULL,
    pinned BOOLEAN NOT NULL,
    views INT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);


CREATE TABLE IF NOT EXISTS activity_post_entries (
    id UUID NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);


CREATE TABLE IF NOT EXISTS meeting_types (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    week_day INT NOT NULL,
    start_at TIME NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS meetings (
    id UUID NOT NULL,
    admin_id UUID NOT NULL,
    content TEXT NOT NULL,
    meeting_type_id BIGINT NOT NULL,
    starts_at TIMESTAMP NOT NULL,
    ends_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE meetings ADD CONSTRAINT fk_meetings_admin_id FOREIGN KEY (admin_id) REFERENCES users(id);
ALTER TABLE meetings ADD CONSTRAINT fk_meetings_meeting_type_id FOREIGN KEY (meeting_type_id) REFERENCES meeting_types(id);

CREATE TABLE IF NOT EXISTS meeting_participants (
    meeting_id UUID NOT NULL,
    user_id UUID NOT NULL,
    attend_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE meeting_participants ADD CONSTRAINT fk_meeting_participants_meeting_id FOREIGN KEY (meeting_id) REFERENCES meetings(id);
ALTER TABLE meeting_participants ADD CONSTRAINT fk_meeting_participants_user_id FOREIGN KEY (user_id) REFERENCES users(id);

CREATE TABLE IF NOT EXISTS feedbacks (
    id UUID NOT NULL,
    sender_id UUID NOT NULL,
    target_id UUID NOT NULL,
    type VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE feedbacks ADD CONSTRAINT fk_feedbacks_sender_id FOREIGN KEY (sender_id) REFERENCES users(id);
ALTER TABLE feedbacks ADD CONSTRAINT fk_feedbacks_target_id FOREIGN KEY (target_id) REFERENCES users(id);

CREATE TABLE IF NOT EXISTS feedback_reviews (
    id UUID NOT NULL,
    feedback_id UUID NOT NULL,
    staff_id UUID NOT NULL,
    status VARCHAR(255) NOT NULL,
    reason TEXT NOT NULL,
    received_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE feedback_reviews ADD CONSTRAINT fk_feedback_reviews_feedback_id FOREIGN KEY (feedback_id) REFERENCES feedbacks(id);
ALTER TABLE feedback_reviews ADD CONSTRAINT fk_feedback_reviews_staff_id FOREIGN KEY (staff_id) REFERENCES users(id);

CREATE TABLE IF NOT EXISTS wallets (
    id UUID NOT NULL,
    owner UUIDMORPHS NOT NULL,
    currency VARCHAR(255) NOT NULL,
    balance INT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);


CREATE TABLE IF NOT EXISTS transactions (
    id UUID NOT NULL,
    wallet_id UUID NOT NULL,
    type VARCHAR(255) NOT NULL,
    amount INT NOT NULL,
    balance_after INT NOT NULL,
    reference NULLABLEUUIDMORPHS NOT NULL,
    description VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE transactions ADD CONSTRAINT fk_transactions_wallet_id FOREIGN KEY (wallet_id) REFERENCES wallets(id);

CREATE TABLE IF NOT EXISTS characters (
    id UUID NOT NULL,
    user_id UUID NOT NULL,
    experience INT NOT NULL,
    reputation INT NOT NULL,
    daily_bonus_claimed_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE characters ADD CONSTRAINT fk_characters_user_id FOREIGN KEY (user_id) REFERENCES users(id);

CREATE TABLE IF NOT EXISTS badges (
    id BIGSERIAL PRIMARY KEY,
    provider VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    redeem_code VARCHAR(255) NOT NULL,
    active BOOLEAN NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS characters_badges (
    character_id UUID NOT NULL,
    badge_id BIGINT NOT NULL,
    claimed_at TIMESTAMP NOT NULL
);

ALTER TABLE characters_badges ADD CONSTRAINT fk_characters_badges_character_id FOREIGN KEY (character_id) REFERENCES characters(id);
ALTER TABLE characters_badges ADD CONSTRAINT fk_characters_badges_badge_id FOREIGN KEY (badge_id) REFERENCES badges(id);

CREATE TABLE IF NOT EXISTS seasons_rankings (
    id UUID NOT NULL,
    season_id VARCHAR(255) NOT NULL,
    character_id UUID NOT NULL,
    ranking_position INT NOT NULL,
    level INT NOT NULL,
    experience INT NOT NULL,
    messages_count INT NOT NULL,
    badges_count INT NOT NULL,
    meetings_count INT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE seasons_rankings ADD CONSTRAINT fk_seasons_rankings_character_id FOREIGN KEY (character_id) REFERENCES characters(id);

CREATE TABLE IF NOT EXISTS seasons (
    id UUID NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    started_at TIMESTAMP NOT NULL,
    ended_at TIMESTAMP NOT NULL,
    messages_count INT NOT NULL,
    participants_count INT NOT NULL,
    meeting_count INT NOT NULL,
    badges_count INT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);


CREATE TABLE IF NOT EXISTS characters_leveling_logs (
    id UUID NOT NULL,
    character_id UUID NOT NULL,
    level INT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE characters_leveling_logs ADD CONSTRAINT fk_characters_leveling_logs_character_id FOREIGN KEY (character_id) REFERENCES characters(id);

CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) NOT NULL,
    user_id UUID NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL
);


CREATE TABLE IF NOT EXISTS characters_wallet (
    id BIGSERIAL PRIMARY KEY,
    balance INT NOT NULL,
    character_id UUID NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);

ALTER TABLE characters_wallet ADD CONSTRAINT fk_characters_wallet_character_id FOREIGN KEY (character_id) REFERENCES characters(id);

CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_donator BOOLEAN NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    first_login_at TIMESTAMP NOT NULL,
    suspended_until TIMESTAMPTZ NOT NULL,
    banned_at TIMESTAMPTZ NOT NULL,
    users_name_unique DROPUNIQUE NOT NULL,
    email_verified_at TIMESTAMP NOT NULL,
    deleted_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);

CREATE INDEX idx_users_b068931cc450442b63f5b3d276ea4297 ON users (name);
ALTER TABLE users ADD CONSTRAINT uniq_users_b068931cc450442b63f5b3d276ea4297 UNIQUE (name);

CREATE TABLE IF NOT EXISTS password_resets (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL
);


CREATE TABLE IF NOT EXISTS providers (
    id UUID NOT NULL,
    provider VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id VARCHAR(255) NOT NULL,
    type VARCHAR(255) NOT NULL,
    credentials_type VARCHAR(255) NOT NULL,
    credentials TEXT NOT NULL,
    connected_by UUID NOT NULL,
    connected_at TIMESTAMP NOT NULL,
    disconnected_at TIMESTAMP NOT NULL,
    metadata JSON NOT NULL,
    external_account_id VARCHAR(255) NOT NULL
);

CREATE INDEX idx_providers_afb333c92bcbc0aa3e2d466851d54080 ON providers (model_type, model_id);
ALTER TABLE providers ADD CONSTRAINT fk_providers_user_id FOREIGN KEY (user_id) REFERENCES users(id);

CREATE TABLE IF NOT EXISTS user_address (
    id UUID NOT NULL,
    user_id UUID NOT NULL,
    country VARCHAR(4) NOT NULL,
    state VARCHAR(4) NOT NULL,
    city VARCHAR(255) NOT NULL,
    zip_code VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE user_address ADD CONSTRAINT fk_user_address_user_id FOREIGN KEY (user_id) REFERENCES users(id);

CREATE TABLE IF NOT EXISTS user_information (
    id UUID NOT NULL,
    user_id UUID NOT NULL,
    name VARCHAR(255) NOT NULL,
    nickname VARCHAR(255) NOT NULL,
    linkedin_url VARCHAR(255) NOT NULL,
    github_url VARCHAR(255) NOT NULL,
    birthdate DATE NOT NULL,
    about TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE user_information ADD CONSTRAINT fk_user_information_user_id FOREIGN KEY (user_id) REFERENCES users(id);

CREATE TABLE IF NOT EXISTS tenants (
    id UUID NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    owner_id UUID NOT NULL,
    active BOOLEAN NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL
);

ALTER TABLE tenants ADD CONSTRAINT fk_tenants_owner_id FOREIGN KEY (owner_id) REFERENCES users(id);

CREATE TABLE IF NOT EXISTS provider_tokens (
    id UUID NOT NULL,
    provider_id UUID NOT NULL,
    access_token VARCHAR(255) NOT NULL,
    refresh_token VARCHAR(255) NOT NULL,
    expires_in INT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE provider_tokens ADD CONSTRAINT fk_provider_tokens_provider_id FOREIGN KEY (provider_id) REFERENCES providers(id);

CREATE TABLE IF NOT EXISTS tenant_users (
    tenant_id UUID NOT NULL,
    user_id UUID NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE tenant_users ADD CONSTRAINT fk_tenant_users_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);
ALTER TABLE tenant_users ADD CONSTRAINT fk_tenant_users_user_id FOREIGN KEY (user_id) REFERENCES users(id);

CREATE TABLE IF NOT EXISTS external_identities (

);


CREATE TABLE IF NOT EXISTS discord_event_logs (
    id BIGSERIAL PRIMARY KEY,
    event_type VARCHAR(255) NOT NULL,
    guild_id VARCHAR(255) NOT NULL,
    user_id VARCHAR(255) NOT NULL,
    channel_id VARCHAR(255) NOT NULL,
    payload JSONB NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS discord_guilds (
    id BIGSERIAL PRIMARY KEY,
    tenant_id UUID NOT NULL,
    discord_guild_id VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    icon VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    member_count INT NOT NULL,
    premium_tier SMALLINT NOT NULL,
    features JSONB NOT NULL,
    synced_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);

ALTER TABLE discord_guilds ADD CONSTRAINT fk_discord_guilds_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);

CREATE TABLE IF NOT EXISTS discord_channels (
    id BIGSERIAL PRIMARY KEY,
    discord_guild_id INDEX NOT NULL,
    discord_channel_id VARCHAR(255) NOT NULL,
    parent_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    type SMALLINT NOT NULL,
    topic TEXT NOT NULL,
    position SMALLINT NOT NULL,
    nsfw BOOLEAN NOT NULL,
    bitrate INT NOT NULL,
    user_limit INT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);

ALTER TABLE discord_channels ADD CONSTRAINT fk_discord_channels_discord_guild_id FOREIGN KEY (discord_guild_id) REFERENCES discord_guilds(id);
ALTER TABLE discord_channels ADD CONSTRAINT fk_discord_channels_parent_id FOREIGN KEY (parent_id) REFERENCES discord_channels(id);

CREATE TABLE IF NOT EXISTS discord_roles (
    id BIGSERIAL PRIMARY KEY,
    discord_guild_id INDEX NOT NULL,
    discord_role_id VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    color INT NOT NULL,
    position SMALLINT NOT NULL,
    permissions BIGINT NOT NULL,
    is_hoisted BOOLEAN NOT NULL,
    is_mentionable BOOLEAN NOT NULL,
    is_managed BOOLEAN NOT NULL,
    icon VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);

ALTER TABLE discord_roles ADD CONSTRAINT fk_discord_roles_discord_guild_id FOREIGN KEY (discord_guild_id) REFERENCES discord_guilds(id);

CREATE TABLE IF NOT EXISTS discord_members (
    id BIGSERIAL PRIMARY KEY,
    discord_guild_id BIGINT NOT NULL,
    discord_user_id INDEX NOT NULL,
    external_identity_id INDEX NOT NULL,
    username VARCHAR(255) NOT NULL,
    global_name VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) NOT NULL,
    nickname VARCHAR(255) NOT NULL,
    is_bot BOOLEAN NOT NULL,
    is_pending BOOLEAN NOT NULL,
    joined_at TIMESTAMP NOT NULL,
    premium_since TIMESTAMP NOT NULL,
    communication_disabled_until TIMESTAMP NOT NULL,
    left_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);

ALTER TABLE discord_members ADD CONSTRAINT fk_discord_members_discord_guild_id FOREIGN KEY (discord_guild_id) REFERENCES discord_guilds(id);
ALTER TABLE discord_members ADD CONSTRAINT fk_discord_members_external_identity_id FOREIGN KEY (external_identity_id) REFERENCES external_identities(id);

CREATE TABLE IF NOT EXISTS discord_member_roles (
    discord_member_id BIGINT NOT NULL,
    discord_role_id BIGINT NOT NULL,
    assigned_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE discord_member_roles ADD CONSTRAINT fk_discord_member_roles_discord_member_id FOREIGN KEY (discord_member_id) REFERENCES discord_members(id);
ALTER TABLE discord_member_roles ADD CONSTRAINT fk_discord_member_roles_discord_role_id FOREIGN KEY (discord_role_id) REFERENCES discord_roles(id);

CREATE TABLE IF NOT EXISTS discord_member_role_history (
    id BIGSERIAL PRIMARY KEY,
    discord_member_id INDEX NOT NULL,
    discord_role_id INDEX NOT NULL,
    action VARCHAR(255) NOT NULL,
    occurred_at INDEX NOT NULL,
    source_event_log_id BIGINT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);

ALTER TABLE discord_member_role_history ADD CONSTRAINT fk_discord_member_role_history_discord_member_id FOREIGN KEY (discord_member_id) REFERENCES discord_members(id);
ALTER TABLE discord_member_role_history ADD CONSTRAINT fk_discord_member_role_history_discord_role_id FOREIGN KEY (discord_role_id) REFERENCES discord_roles(id);
ALTER TABLE discord_member_role_history ADD CONSTRAINT fk_discord_member_role_history_source_event_log_id FOREIGN KEY (source_event_log_id) REFERENCES discord_event_logs(id);

CREATE TABLE IF NOT EXISTS twitch_event_logs (
    id BIGSERIAL PRIMARY KEY,
    event_type VARCHAR(255) NOT NULL,
    broadcaster_user_id VARCHAR(255) NOT NULL,
    user_id VARCHAR(255) NOT NULL,
    twitch_message_id VARCHAR(255) NOT NULL,
    payload JSONB NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    tenant_id DROPCONSTRAINEDFOREIGNID NOT NULL,
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS twitch_subscriptions (
    id BIGSERIAL PRIMARY KEY,
    subscription_id VARCHAR(255) NOT NULL,
    type INDEX NOT NULL,
    status VARCHAR(255) NOT NULL,
    broadcaster_user_id INDEX NOT NULL,
    condition JSONB NOT NULL,
    transport VARCHAR(255) NOT NULL,
    callback_url VARCHAR(255) NOT NULL,
    cost INT NOT NULL,
    version VARCHAR(255) NOT NULL,
    tenant_id INDEX NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS moderation_cases (
    id UUID NOT NULL,
    content_type VARCHAR(50) NOT NULL,
    content_id VARCHAR(255) NOT NULL,
    content_snapshot JSONB NOT NULL,
    source_platform VARCHAR(20) NOT NULL,
    source VARCHAR(20) NOT NULL,
    status VARCHAR(20) NOT NULL,
    priority INT NOT NULL,
    severity VARCHAR(20) NOT NULL,
    violation_type VARCHAR(30) NOT NULL,
    ai_scores JSONB NOT NULL,
    classifier_version VARCHAR(50) NOT NULL,
    suggested_action VARCHAR(30) NOT NULL,
    assigned_to UUID NOT NULL,
    assigned_at TIMESTAMPTZ NOT NULL,
    resolved_at TIMESTAMPTZ NOT NULL,
    author_id UUID NOT NULL,
    tenant_id UUID NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE moderation_cases ADD CONSTRAINT fk_moderation_cases_assigned_to FOREIGN KEY (assigned_to) REFERENCES users(id);
ALTER TABLE moderation_cases ADD CONSTRAINT fk_moderation_cases_author_id FOREIGN KEY (author_id) REFERENCES users(id);
ALTER TABLE moderation_cases ADD CONSTRAINT fk_moderation_cases_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);

CREATE TABLE IF NOT EXISTS moderation_reports (
    id UUID NOT NULL,
    case_id UUID NOT NULL,
    reporter_id UUID NOT NULL,
    reason VARCHAR(30) NOT NULL,
    details TEXT NOT NULL,
    platform VARCHAR(20) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL
);

ALTER TABLE moderation_reports ADD CONSTRAINT fk_moderation_reports_case_id FOREIGN KEY (case_id) REFERENCES moderation_cases(id);
ALTER TABLE moderation_reports ADD CONSTRAINT fk_moderation_reports_reporter_id FOREIGN KEY (reporter_id) REFERENCES users(id);

CREATE TABLE IF NOT EXISTS moderation_actions (
    id UUID NOT NULL,
    case_id UUID NOT NULL,
    moderator_id UUID NOT NULL,
    action_type VARCHAR(30) NOT NULL,
    target_platforms JSONB NOT NULL,
    duration VARCHAR(20) NOT NULL,
    reason TEXT NOT NULL,
    metadata JSONB NOT NULL,
    execution_results JSONB NOT NULL,
    automated BOOLEAN NOT NULL,
    created_at TIMESTAMPTZ NOT NULL,
    tenant_id DROPCONSTRAINEDFOREIGNID NOT NULL
);

ALTER TABLE moderation_actions ADD CONSTRAINT fk_moderation_actions_case_id FOREIGN KEY (case_id) REFERENCES moderation_cases(id);
ALTER TABLE moderation_actions ADD CONSTRAINT fk_moderation_actions_moderator_id FOREIGN KEY (moderator_id) REFERENCES users(id);
ALTER TABLE moderation_actions ADD CONSTRAINT fk_moderation_actions_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);

CREATE TABLE IF NOT EXISTS moderation_appeals (
    id UUID NOT NULL,
    action_id UUID NOT NULL,
    appellant_id UUID NOT NULL,
    reason_category VARCHAR(50) NOT NULL,
    reason_text TEXT NOT NULL,
    status VARCHAR(20) NOT NULL,
    reviewer_id UUID NOT NULL,
    reviewer_notes TEXT NOT NULL,
    resolved_at TIMESTAMPTZ NOT NULL,
    sla_deadline TIMESTAMPTZ NOT NULL,
    created_at TIMESTAMPTZ NOT NULL,
    tenant_id DROPCONSTRAINEDFOREIGNID NOT NULL
);

ALTER TABLE moderation_appeals ADD CONSTRAINT fk_moderation_appeals_action_id FOREIGN KEY (action_id) REFERENCES moderation_actions(id);
ALTER TABLE moderation_appeals ADD CONSTRAINT fk_moderation_appeals_appellant_id FOREIGN KEY (appellant_id) REFERENCES users(id);
ALTER TABLE moderation_appeals ADD CONSTRAINT fk_moderation_appeals_reviewer_id FOREIGN KEY (reviewer_id) REFERENCES users(id);
ALTER TABLE moderation_appeals ADD CONSTRAINT fk_moderation_appeals_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);

CREATE TABLE IF NOT EXISTS moderation_rules (
    id UUID NOT NULL,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(20) NOT NULL,
    platform VARCHAR(20) NOT NULL,
    pattern TEXT NOT NULL,
    violation_type VARCHAR(30) NOT NULL,
    severity VARCHAR(20) NOT NULL,
    action_on_match VARCHAR(30) NOT NULL,
    is_active BOOLEAN NOT NULL,
    tenant_id UUID NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE moderation_rules ADD CONSTRAINT fk_moderation_rules_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);

CREATE TABLE IF NOT EXISTS moderation_audit_log (
    id BIGSERIAL PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    actor_id UUID NOT NULL,
    actor_type VARCHAR(20) NOT NULL,
    case_id UUID NOT NULL,
    details JSONB NOT NULL,
    platform VARCHAR(20) NOT NULL,
    tenant_id UUID NOT NULL,
    created_at TIMESTAMPTZ NOT NULL,
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS notifications (
    notifiable_id BIGINT NOT NULL,
    id UUID NOT NULL,
    type VARCHAR(255) NOT NULL,
    notifiable MORPHS NOT NULL,
    data TEXT NOT NULL,
    read_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);


CREATE TABLE IF NOT EXISTS user_profiles (
    id UUID NOT NULL,
    user_id UUID NOT NULL,
    tenant_id UUID NOT NULL,
    nickname VARCHAR(255) NOT NULL,
    birthdate DATE NOT NULL,
    about TEXT NOT NULL,
    headline VARCHAR(255) NOT NULL,
    seniority_level VARCHAR(30) NOT NULL,
    years_experience SMALLINT NOT NULL,
    social_links JSONB NOT NULL,
    available_for_proposals BOOLEAN NOT NULL,
    start_availability VARCHAR(30) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

ALTER TABLE user_profiles ADD CONSTRAINT fk_user_profiles_user_id FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE user_profiles ADD CONSTRAINT fk_user_profiles_tenant_id FOREIGN KEY (tenant_id) REFERENCES tenants(id);

CREATE TABLE IF NOT EXISTS failed_jobs (
    id BIGSERIAL PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL,
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id BIGSERIAL PRIMARY KEY,
    tokenable MORPHS NOT NULL,
    name TEXT NOT NULL,
    token VARCHAR(64) NOT NULL,
    abilities TEXT NOT NULL,
    last_used_at TIMESTAMP NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS cache (
    key VARCHAR(255) NOT NULL,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL
);


CREATE TABLE IF NOT EXISTS cache_locks (
    key VARCHAR(255) NOT NULL,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
);


CREATE TABLE IF NOT EXISTS job_batches (
    id VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NOT NULL,
    cancelled_at INT NOT NULL,
    created_at INT NOT NULL,
    finished_at INT NOT NULL
);


CREATE TABLE IF NOT EXISTS jobs (
    id BIGINT AUTO_INCREMENT NOT NULL,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT NOT NULL,
    reserved_at INT NOT NULL,
    available_at INT NOT NULL,
    created_at INT NOT NULL
);


CREATE TABLE IF NOT EXISTS media (
    id BIGSERIAL PRIMARY KEY,
    model_type VARCHAR(255) NOT NULL,
    model_id VARCHAR(255) NOT NULL,
    collection_name VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(255) NOT NULL,
    disk VARCHAR(255) NOT NULL,
    conversions_disk VARCHAR(255) NOT NULL,
    size BIGINT NOT NULL,
    manipulations JSON NOT NULL,
    custom_properties JSON NOT NULL,
    generated_conversions JSON NOT NULL,
    responsive_images JSON NOT NULL,
    order_column INT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS addresses (
    id UUID NOT NULL,
    addressable UUIDMORPHS NOT NULL,
    country VARCHAR(4) NOT NULL,
    state VARCHAR(4) NOT NULL,
    city VARCHAR(255) NOT NULL,
    zip_code VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);


CREATE TABLE IF NOT EXISTS exports (
    id BIGSERIAL PRIMARY KEY,
    completed_at TIMESTAMP NOT NULL,
    file_disk VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    exporter VARCHAR(255) NOT NULL,
    processed_rows INT NOT NULL,
    total_rows INT NOT NULL,
    successful_rows INT NOT NULL,
    user_id BIGINT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS failed_import_rows (
    id BIGSERIAL PRIMARY KEY,
    data JSON NOT NULL,
    import_id BIGINT NOT NULL,
    validation_error TEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS imports (
    id BIGSERIAL PRIMARY KEY,
    completed_at TIMESTAMP NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    importer VARCHAR(255) NOT NULL,
    processed_rows INT NOT NULL,
    total_rows INT NOT NULL,
    successful_rows INT NOT NULL,
    user_id BIGINT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (id)
);


CREATE TABLE IF NOT EXISTS phpdebugbar (
    id PRIMARY NOT NULL,
    data LONGTEXT NOT NULL,
    meta_utime INDEX NOT NULL,
    meta_datetime INDEX NOT NULL,
    meta_uri INDEX NOT NULL,
    meta_ip INDEX NOT NULL,
    meta_method INDEX NOT NULL
);


