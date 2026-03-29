import React from 'react';

interface StatItem {
  icon: string;
  value: string;
  label: string;
}

interface StatsBarProps {
  stats: StatItem[];
}

export const StatsBar: React.FC<StatsBarProps> = ({ stats }) => {
  return (
    <div className="stats-bar">
      {stats.map((stat, index) => (
        <div key={index} className="stat-item">
          <div className="stat-icon">{stat.icon}</div>
          <div className="stat-value">{stat.value}</div>
          <div className="stat-label">{stat.label}</div>
        </div>
      ))}
    </div>
  );
};

StatsBar.displayName = 'StatsBar';

interface CardGridProps {
  cards: Array<{
    icon: string;
    title: string;
    description: string;
    count?: string;
    link?: string;
  }>;
}

export const CardGrid: React.FC<CardGridProps> = ({ cards }) => {
  return (
    <div className="grid">
      {cards.map((card, index) => (
        <div key={index} className="grid-item card">
          <div className="card-image">{card.icon}</div>
          <div className="card-content">
            <h3>{card.title}</h3>
            <p className="card-description">{card.description}</p>
            {card.count && <span className="card-count">{card.count}</span>}
            {card.link && (
              <a href={card.link} className="card-link">
                Explore →
              </a>
            )}
          </div>
        </div>
      ))}
    </div>
  );
};

CardGrid.displayName = 'CardGrid';
