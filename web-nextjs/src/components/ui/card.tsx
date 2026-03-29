import React from 'react';

interface CardProps extends React.HTMLAttributes<HTMLDivElement> {
  icon?: string;
  title?: string;
  description?: string;
  count?: string;
  link?: string;
}

export const Card = React.forwardRef<HTMLDivElement, CardProps>(
  ({ icon, title, description, count, link, children, ...props }, ref) => {
    return (
      <div
        ref={ref}
        className="card"
        {...props}
      >
        {icon && (
          <div className="card-image">
            <span>{icon}</span>
          </div>
        )}
        <div className="card-content">
          {icon && <div className="card-icon">{icon}</div>}
          {title && <h3>{title}</h3>}
          {description && <p className="card-description">{description}</p>}
          {count && <span className="card-count">{count}</span>}
          {children}
          {link && (
            <a href={link} className="card-link">
              Explore →
            </a>
          )}
        </div>
      </div>
    );
  }
);

Card.displayName = 'Card';
