import React from 'react';

interface BadgeProps extends React.HTMLAttributes<HTMLSpanElement> {
  variant?: 'default' | 'success' | 'warning' | 'error';
}

export const Badge = React.forwardRef<HTMLSpanElement, BadgeProps>(
  ({ variant = 'default', children, ...props }, ref) => {
    const variantClasses = {
      default: 'bg-accent-teal text-dark-bg',
      success: 'bg-accent-emerald text-white',
      warning: 'bg-accent-orange text-dark-bg',
      error: 'bg-accent-rose text-white',
    };

    return (
      <span
        ref={ref}
        className={`inline-block px-3 py-1 rounded-full text-sm font-semibold ${variantClasses[variant]}`}
        {...props}
      >
        {children}
      </span>
    );
  }
);

Badge.displayName = 'Badge';

interface InputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
}

export const Input = React.forwardRef<HTMLInputElement, InputProps>(
  ({ label, error, className, ...props }, ref) => {
    return (
      <div className="w-full">
        {label && (
          <label className="block text-sm font-semibold text-text-primary mb-2">
            {label}
          </label>
        )}
        <input
          ref={ref}
          className={`w-full px-4 py-2 bg-dark-card border border-border-color rounded-lg text-text-primary focus:border-accent-teal focus:ring-2 focus:ring-accent-teal focus:ring-opacity-20 transition-all ${className}`}
          {...props}
        />
        {error && (
          <p className="mt-1 text-sm text-accent-rose">{error}</p>
        )}
      </div>
    );
  }
);

Input.displayName = 'Input';
