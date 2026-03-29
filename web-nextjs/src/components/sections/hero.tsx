import React from 'react';
import { Button } from './ui';

interface HeroProps {
  badge?: string;
  title: string;
  description: string;
  primaryAction?: {
    label: string;
    href: string;
  };
  secondaryAction?: {
    label: string;
    href: string;
  };
}

export const Hero: React.FC<HeroProps> = ({
  badge,
  title,
  description,
  primaryAction,
  secondaryAction,
}) => {
  return (
    <section className="hero">
      <div className="hero-content">
        {badge && (
          <div className="hero-badge">
            {badge}
          </div>
        )}
        <h1>{title}</h1>
        <p>{description}</p>
        <div className="hero-actions">
          {primaryAction && (
            <Button
              variant="primary"
              size="lg"
              onClick={() => window.location.href = primaryAction.href}
            >
              {primaryAction.label}
            </Button>
          )}
          {secondaryAction && (
            <Button
              variant="secondary"
              size="lg"
              onClick={() => window.location.href = secondaryAction.href}
            >
              {secondaryAction.label}
            </Button>
          )}
        </div>
      </div>
    </section>
  );
};

Hero.displayName = 'Hero';
