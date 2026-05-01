-- ══════════════════════════════════════════
-- BATTLE FOR THE INTERNET SOUL — Supabase Schema
-- Run this in Supabase SQL Editor
-- ══════════════════════════════════════════

-- ── CONTENT TABLE ──
-- Stores all prayers and curses available for selection
CREATE TABLE content (
  id          UUID DEFAULT gen_random_uuid() PRIMARY KEY,
  created_at  TIMESTAMPTZ DEFAULT NOW(),
  type        TEXT NOT NULL CHECK (type IN ('prayer', 'curse')),
  category    TEXT NOT NULL,
  title       TEXT NOT NULL,
  text        TEXT NOT NULL,
  source      TEXT,          -- e.g. 'Psalms 23', 'Deuteronomy 28', 'Celtic tradition'
  tier        TEXT DEFAULT 'topical' CHECK (tier IN ('random', 'topical')),
  active      BOOLEAN DEFAULT TRUE
);

-- ── TRANSACTIONS TABLE ──
-- Every paid prayer or curse sent
CREATE TABLE transactions (
  id               UUID DEFAULT gen_random_uuid() PRIMARY KEY,
  created_at       TIMESTAMPTZ DEFAULT NOW(),
  type             TEXT NOT NULL CHECK (type IN ('prayer', 'curse')),
  tier             TEXT NOT NULL CHECK (tier IN ('random', 'topical', 'custom')),
  price_paid       NUMERIC(6,2) NOT NULL,
  sender_name      TEXT NOT NULL,
  sender_email     TEXT,
  recipient_name   TEXT NOT NULL,
  recipient_email  TEXT NOT NULL,
  content_id       UUID REFERENCES content(id) ON DELETE SET NULL,
  custom_text      TEXT,      -- used when tier = 'custom'
  paypal_order_id  TEXT,
  slug             TEXT UNIQUE NOT NULL,
  delivered        BOOLEAN DEFAULT FALSE,
  view_count       INTEGER DEFAULT 0
);

-- ── INDEXES ──
CREATE INDEX idx_transactions_slug     ON transactions(slug);
CREATE INDEX idx_transactions_type     ON transactions(type);
CREATE INDEX idx_transactions_created  ON transactions(created_at DESC);
CREATE INDEX idx_content_type_category ON content(type, category, active);

-- ── ROW LEVEL SECURITY ──
ALTER TABLE content      ENABLE ROW LEVEL SECURITY;
ALTER TABLE transactions ENABLE ROW LEVEL SECURITY;

-- Content: anyone can read active content (for dropdowns)
CREATE POLICY "Public can read active content"
  ON content FOR SELECT
  USING (active = TRUE);

-- Transactions: anyone can insert (payment already happened)
CREATE POLICY "Public can insert transactions"
  ON transactions FOR INSERT
  WITH CHECK (TRUE);

-- Transactions: public can read by slug (for landing pages)
CREATE POLICY "Public can read transaction by slug"
  ON transactions FOR SELECT
  USING (TRUE);

-- Transactions: public can increment view count
CREATE POLICY "Public can update view count"
  ON transactions FOR UPDATE
  USING (TRUE)
  WITH CHECK (TRUE);

-- ── SEED DATA: PRAYERS ──
INSERT INTO content (type, category, title, text, source, tier) VALUES

-- General Blessing
('prayer','General Blessing','The Lord Bless You and Keep You',
'The Lord bless you and keep you; the Lord make his face shine on you and be gracious to you; the Lord turn his face toward you and give you peace.',
'Numbers 6:24-26','topical'),

('prayer','General Blessing','May Angels Guard Your Path',
'May the road rise up to meet you. May the wind be always at your back. May the sun shine warm upon your face, the rains fall soft upon your fields, and until we meet again, may God hold you in the palm of his hand.',
'Traditional Irish Blessing','topical'),

('prayer','General Blessing','A Morning Blessing',
'This is the day that the Lord has made. May you rejoice and be glad in it. May fresh mercies greet you at every turn, and may you know in the depths of your heart that you are seen, loved, and not forgotten.',
'Original, after Psalm 118:24','topical'),

-- Love & Relationships
('prayer','Love & Relationships','A Blessing Upon a New Love',
'May the love between you be like a deep river — quiet on the surface, but powerful in its current. May you be patient with each other''s faults and generous with each other''s strengths. May you grow old together in laughter.',
'Original','topical'),

('prayer','Love & Relationships','A Prayer for Mending',
'Where there has been hurt, may there be healing. Where there has been silence, may there be honest words spoken in kindness. May the bonds that were frayed be woven stronger, and may what was broken become the strongest part.',
'Original','topical'),

-- Health & Healing
('prayer','Health & Healing','A Prayer for the Sick',
'Lord, look upon your servant in their suffering. Strengthen what is weak, restore what is broken, and grant the healing of body, mind, and spirit. And whether the healing comes in the way we ask or in ways we cannot yet see, may your peace surpass all understanding.',
'After Philippians 4:7','topical'),

('prayer','Health & Healing','Psalm 23',
'The Lord is my shepherd; I shall not want. He makes me lie down in green pastures. He leads me beside still waters. He restores my soul. He leads me in paths of righteousness for his name''s sake. Even though I walk through the valley of the shadow of death, I will fear no evil, for you are with me.',
'Psalm 23:1-4','topical'),

-- Career & Success
('prayer','Career & Success','A Blessing Upon Good Work',
'May the work of your hands be blessed. May you find purpose in your labor and dignity in your craft. May opportunities open before you like doors that have been waiting for your particular knock. And may your efforts bear fruit in ways you do not yet imagine.',
'Original','topical'),

-- Enemy & Rivals
('prayer','Enemy & Rivals','A Prayer for a Difficult Person',
'I pray for those who have wronged me. Not for their punishment, but for their understanding. May they come to know the harm they''ve caused. May we both be released from what binds us in conflict. And if peace between us is not possible, may we at least know peace within ourselves.',
'After Matthew 5:44','topical'),

-- Grief & Loss
('prayer','Grief & Loss','A Prayer for the Grieving',
'For those who grieve, may there be comfort. For those who have lost, may there be the memory of joy. May the empty space not be only emptiness, but also the shape of what was deeply loved. And may the time come when it becomes possible to smile at what was, even through tears.',
'Original','topical'),

-- Sports Victory
('prayer','Sports Victory','A Prayer Before the Game',
'May you play with the full use of your gifts. May your preparation meet this moment with grace. May you compete with honor and with joy, and may the result — whatever it is — leave you with your dignity and your friendship intact. And also, Lord willing, may you win.',
'Original','topical'),

-- Biblical (random-eligible classics)
('prayer','Biblical','The Lord''s Prayer',
'Our Father, who art in heaven, hallowed be thy name. Thy kingdom come, thy will be done, on earth as it is in heaven. Give us this day our daily bread, and forgive us our trespasses, as we forgive those who trespass against us. Lead us not into temptation, but deliver us from evil.',
'Matthew 6:9-13','random'),

('prayer','Biblical','Psalm 91',
'He who dwells in the shelter of the Most High will rest in the shadow of the Almighty. I will say of the Lord, He is my refuge and my fortress, my God, in whom I trust. Surely he will save you from the fowler''s snare and from the deadly pestilence. He will cover you with his feathers.',
'Psalm 91:1-4','random'),

('prayer','Biblical','A Benediction',
'May the God of hope fill you with all joy and peace as you trust in him, so that you may overflow with hope by the power of the Holy Spirit.',
'Romans 15:13','random');

-- ── SEED DATA: CURSES ──
INSERT INTO content (type, category, title, text, source, tier) VALUES

-- Biblical Smiting
('curse','Biblical Smiting','Deuteronomy''s Full Fury',
'The Lord shall smite thee with a consumption, and with a fever, and with an inflammation, and with an extreme burning, and with the sword, and with blasting, and with mildew; and they shall pursue thee until thou perish.',
'Deuteronomy 28:22','topical'),

('curse','Biblical Smiting','The Botch of Egypt',
'The Lord shall smite thee with the botch of Egypt, and with the emerods, and with the scab, and with the itch, whereof thou canst not be healed. The Lord shall smite thee with madness, and blindness, and astonishment of heart.',
'Deuteronomy 28:27-28','topical'),

('curse','Biblical Smiting','The Imprecatory Psalm',
'Let his days be few; and let another take his office. Let his children be fatherless, and his wife a widow. Let his children be continually vagabonds, and beg: let them seek their bread also out of their desolate places.',
'Psalm 109:8-10','topical'),

-- Ancient Celtic
('curse','Ancient Celtic','The Raven''s Curse',
'May the raven find your name. May your sleep be broken, your waking troubled. May what you planted wither. May the crow remember what you have done when it matters most that you be forgotten.',
'Celtic cursing tradition, adapted','topical'),

('curse','Ancient Celtic','Stone and Water',
'I write your name on stone and cast it into dark water. As the stone sinks, may your fortunes descend. As the cold closes over it, may warmth leave your dealings. As it lies unseen in the deep, may your plans go equally unregarded.',
'After Roman defixio / Celtic tradition','topical'),

-- General Wrath
('curse','General Wrath','A Measured Damnation',
'May your Wi-Fi forever be one bar. May your coffee always cool before you reach it. May your phone die at 12% when you need it most. And may those who have wronged you feel, in some small but persistent way, the precise caliber of their wrongdoing.',
'Original — InternetSatan.com','topical'),

('curse','General Wrath','The Full Reckoning',
'May every minor inconvenience you have caused another return to you threefold. May you stub your toe in the dark with some regularity. May parking be elusive. May your enemies'' problems become annoyingly visible to you and yet entirely out of your reach to resolve.',
'Original — InternetSatan.com','topical'),

-- Betrayal
('curse','Betrayal','For the Betrayer',
'You who took the trust offered to you and turned it into a weapon — may that weapon grow heavy. May you be known for what you are. May the story you tell about yourself find fewer and fewer willing listeners, until even you grow tired of hearing it.',
'Original','topical'),

-- Romantic Rival
('curse','Romantic Rival','The Romantic Curse',
'May your love be unrequited in the precise way you have made love unrequited in others. May your texts be left on read. May you wait and not be waited for. And may you come, in time, to understand what you have done — not through punishment, but through memory.',
'Original','topical'),

-- Career Sabotage
('curse','Career Sabotage','The Professional Curse',
'May your emails be replied to slowly, if at all. May your presentations suffer technical difficulties at crucial moments. May the credit you claim be questioned, and the credit due to others be remembered. May your professional reputation eventually reflect your actual character.',
'Original','topical'),

-- Road Rage
('curse','Road Rage','The Road Curse',
'You, who cut me off — may every light ahead of you turn red. May your GPS recalculate at every turn. May you always be in the wrong lane when the toll booth approaches. May the parking spot you rush toward be occupied by someone who got there honestly.',
'Original','topible'),

-- Random classics
('curse','Biblical Smiting','Go, and Sin No More — Except You',
'Cursed shalt thou be in the city, and cursed shalt thou be in the field. Cursed shall be thy basket and thy store. Cursed shall be the fruit of thy body, and the fruit of thy land. Thou shalt be cursed when thou comest in, and cursed when thou goest out.',
'Deuteronomy 28:16-19','random'),

('curse','General Wrath','The Original Hex',
'May fortune forget your name. May luck step around you in the hallway without acknowledgment. May your plans be reasonable and your outcomes unreasonable. May the universe remain technically fair in all its dealings with you and yet somehow never deliver the good news first.',
'Original — InternetSatan.com','random');
